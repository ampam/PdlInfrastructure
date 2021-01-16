//var fs = require( 'fs' );
const fs = require( 'fs-extra' );
const path = require( 'path' );
const extend = require( 'extend' );
const child_process = require( 'child_process' );
const glob = require( 'glob' );
require('colors');

const pdlUtils = require( './utils' );
const verboseLog = pdlUtils.verboseLog;

const fileTimes = require( './fileTimes' );
const configs = require( './config' );

/**
 *
 * @type {FileTimes}
 */
let pdlFileTimes = null;

require( 'colors' );

const cwd = process.cwd();
let childInstances = 0;
let compiler = "pdlc2.exe";
let rebuild = false;

/**
 *
 * @type {PdlConfig}
 */
let pdlConfig = new configs.PdlConfig();

let configFile = path.join( cwd, 'pdl.config.js' );

let globalExitCode = 0;

processCommandLine();
run();


/**
 *
 */
function run() {

    pdlConfig = require( configFile );

    processConfig();


    if ( rebuild )
    {
        cleanTemporal();
        cleanOutput();
    }

    pdlFileTimes = fileTimes.createFileTimes( path.join( pdlConfig.tempDir, "pdlFileTimes.json" ) );
    pdlFileTimes.read();

    console.log('>>>>Begin PDL compilation and generation');
    compileSections();
    pdlFileTimes.write();

    console.log( 'pdl files compiled: ' + childInstances.toString().green.bold + ' file(s)' );
    console.log('<<<<End PDL compilation and generation');
    console.log('');

    if ( globalExitCode === 0 )
    {
        console.log( '>>>>Begin JS Processing' );
        const jsProcessor = require( './jsProcessor' );
        jsProcessor.run( pdlConfig );
        console.log( '<<<<END JS Processing' );
    }

    process.exit( globalExitCode );
}

/**
 *
 */
function processConfig() {
    pdlUtils.setVerbose( pdlConfig.verbose );

    if ( pdlConfig.compilerPath )
    {
        compiler = path.join( pdlConfig.compilerPath, compiler );
    }

    if ( !rebuild )
    {
        rebuild = pdlConfig.rebuild;
    }
}

/**
 *
 */
function cleanTemporal() {
    console.log( 'Cleaning temporal...' );
    pdlUtils.cleanDir( pdlConfig.tempDir );
}

/**
 *
 */
function processCommandLine() {
    for ( let i = 2; i < process.argv.length; i++ )
    {
        const argument = process.argv[ i ];
        const parts = argument.split( '=' );

        const option = parts[ 0 ].trim();

        const value = parts.length > 1
            ? parts[ 1 ].trim()
            : '';

        switch ( option )
        {
            case '--config':
                configFile = value;
                break;

            case '--rebuild':
                rebuild = true;
                break;
        }
    }
}

/**
 *
 */
function cleanOutput() {
    console.log( 'Cleaning output...' );
    pdlUtils.cleanDir( pdlConfig.outputDir );
}

/**
 *
 * @param exclusionList
 * @param file
 * @returns {boolean}
 */
function isInExclusionList( exclusionList, file ) {
    let result = false;
    for ( let i = 0; i < exclusionList.length && !result; i++ )
    {
        result = file.indexOf( exclusionList[ i ] ) >= 0;
    }
    return result;
}

/**
 *
 * @param section
 * @param profileName
 * @param fileOrPattern
 * @returns {Array}
 */
function unwind( section, profileName, fileOrPattern ) {
    let result = [];

    const profile = pdlConfig.profiles[ profileName ];
    const sourcePaths = (section.src || []).concat( profile.src || [] ).concat( pdlConfig.src || [] );

    const exclusionList = section.files[ profileName + 'Exclude' ] || [];

    for ( let i = 0; i < sourcePaths.length && result.length <= 0; i++ )
    {
        /**
         * @type string[]
         */
        const files = glob.sync( path.join( sourcePaths[ i ], fileOrPattern ) );
        files.forEach( function( file ) {
            if ( !isInExclusionList( exclusionList, file ) )
            {
                result.push( file );
            }
        } );
    }

    return result;
}




/**
 *
 * @param value
 */
function errorLog( value ) {
    console.log( value.red.bold );
}


/**
 *
 * @param file
 * @param commandArguments
 */
function processFile( file, commandArguments ) {
    if ( fs.existsSync( file ) )
    {
        if ( pdlFileTimes.isFileModified( file ) )
        {
            const exitCode = compileFile( file, commandArguments );
            if ( exitCode === 0 )
            {
                pdlFileTimes.addFile( file )
            }
            else
            {
                globalExitCode = exitCode;
            }
        }
        else
        {
            verboseLog( file + ' not modified' )
        }
    }
    else
    {
        errorLog( 'File not found:' + file.red.bold + '\n' );
    }
}

/**
 *
 * @param profileName
 * @param section
 */
function compileSectionProfile( profileName, section ) {

    const profile = pdlConfig.profiles[ profileName ];
    const files = section.files[ profileName ];

    let templates = {
        dir: '',
        name: ''
    };

    templates = extend( true,
        templates,
        pdlConfig.templates || {},
        profile.templates || {} );

    const commandArguments = [
        templates.dir,
        templates.name,
        section.outputDir || profile.outputDir || pdlConfig.outputDir,
        profile.configFile
    ];

    files.forEach( function( fileOrPattern ) {

        const singleFiles = unwind( section, profileName, fileOrPattern );

        singleFiles.forEach( function( file ) {
            processFile( file, commandArguments );
        } );

    } );

}

/**
 *
 */
function compileSections() {

    pdlConfig.sections.forEach( function( section ) {
        console.log( ("Compiling " + section.name + "...").cyan.bold );

        for ( const profileName in section.files )
        {
            if ( section.files.hasOwnProperty( profileName ) )
            {
                if ( !profileName.endsWith( 'Exclude' ) )
                {
                    if ( pdlConfig.profiles.hasOwnProperty( profileName ) )
                    {
                        compileSectionProfile( profileName, section );
                    }
                    else
                    {
                        console.error( "Invalid profile: " + profileName );
                    }
                }
            }
        }

    } );
}

/**
 *
 * @param file
 * @param {Array} commandArguments
 * @return {int}
 */
function compileFile( file, commandArguments ) {

    const name = path.parse( file );

    let expandedArguments = commandArguments.map( function( argument ) {
        // noinspection UnnecessaryLocalVariableJS
        const result = argument.replace( "[inputFile]", name );
        return result;
    } );

    expandedArguments.unshift( file );

    childInstances++;

    verboseLog( compiler + " " + expandedArguments.join(' ') );

    const spawnResult = child_process.spawnSync( compiler, expandedArguments,
        {
            cwd: cwd
        } );

    process.stdout.write( spawnResult.stdout.toString() );
    process.stderr.write( spawnResult.stderr.toString() );

    if ( spawnResult.status !== 0 )
    {
        errorLog( 'Compiler Error: code ' + spawnResult.status.toString() + ", file: " + file );
    }

    return spawnResult.status;

}
