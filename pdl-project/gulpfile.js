const fs = require( 'fs-extra' );
const gulp = require( 'gulp' );
const flatten = require( 'gulp-flatten' );
const { task } = gulp;
const newer = require( 'gulp-newer' );
const path = require( 'path' );
const rimraf = require( 'rimraf' );
const childProcess = require( 'child_process' );

// noinspection ES6UnusedImports
require( 'colors' );

const dotenv = require( 'dotenv' );
dotenv.config();

const outputDir = process.env.PDL_OUTPUT;

let doCleanDest = false;
let doRebuild = false;

const pdlConfig = require( './pdl.config' );

function copyDb2Pdl_2Source() {

    const dest = path.join( __dirname, 'src', pdlConfig.db2Pdl.pdl.db2PdlSourceDest );
    fs.ensureDirSync( dest );
    tryCleanDest( dest );
    // noinspection JSCheckFunctionSignatures
    return gulp.src( path.join( process.env.PDL_DB2PDL_OUTPUT, 'pdl/*.pdl' ) )
        //.pipe( newer( dest ) )
        .pipe( flatten() )
        .pipe( gulp.dest( dest ) );
}

function copyGeneratedJs() {
    const dest = process.env.GEN_OUTPUT_JS;
    fs.ensureDirSync( dest );
    tryCleanDest( dest );

    // noinspection JSCheckFunctionSignatures
    return gulp.src( [
        path.join( outputDir, 'js/**/*.js' ),
        '!' + path.join( outputDir, '/js/!**!/index.js' )
    ] )
        //.pipe( flatten() )
        .pipe( newer( dest ) )
        .pipe( gulp.dest( dest ) );
}

function copyCompiledPhp() {
    const dest = process.env.GEN_OUTPUT_PHP;
    fs.ensureDirSync( dest );
    tryCleanDest( dest );

    // noinspection JSCheckFunctionSignatures
    return gulp.src( path.join( process.env.PDL_OUTPUT, 'php/**/*.php' ) )
        .pipe( flatten() )
        .pipe( gulp.dest( dest ) );
}

function copyGeneratedPhpDb() {

    let dirParts = pdlConfig.db2Pdl.pdl.db2PdlSourceDest.split( '/' );

    for ( let i = 0; i < dirParts.length; i++ )
    {
        dirParts[ i ] = dirParts[ i ].charAt( 0 ).toUpperCase() + dirParts[ i ].substr( 1 );
    }

    const dbClassesDir = dirParts.join( '/' );
    const dest = path.join( process.env.GEN_OUTPUT_PHP, dbClassesDir );

    fs.ensureDirSync( dest );
    tryCleanDest( dest );

    // noinspection JSCheckFunctionSignatures
    return gulp.src( path.join( process.env.PDL_DB2PDL_OUTPUT, 'php/**/*.php' ) )
        .pipe( flatten() )
        .pipe( gulp.dest( dest ) );
}

function webpackCopy() {
    const dest = process.env.GEN_OUTPUT_BUNDLE;
    fs.ensureDirSync( dest );
    tryCleanDest( dest );
    // noinspection JSCheckFunctionSignatures
    return gulp.src( path.join( process.env.PDL_OUTPUT, 'webpack/bundles/**/*' ) )
        .pipe( flatten() )
        .pipe( gulp.dest( dest ) );
}

function postBuildCopy() {
    return new Promise( resolve => {
        copyCompiledPhp().on( 'end', () => {
            copyGeneratedPhpDb().on( 'end', () => {
                copyGeneratedJs().on( 'end', () => {
                    webpackCopy().on( 'end', resolve )
                } )
            } )
        } );
    } )
}

function preBuildSteps() {
    return new Promise( resolve => {
        copyDb2Pdl_2Source().on( 'end', resolve );
    } )
}

function postRebuild() {
    doCleanDest = true
    webpackRun();
    return postBuildCopy();
}

/**
 *
 * @param {Array} nodeParams
 * @param {Boolean} exitOnError
 * @returns {*}
 */
function runNodeSync( nodeParams, exitOnError ) {

    const spawnResult = childProcess.spawnSync( 'node', nodeParams,
        {
            cwd: process.cwd(),
            stdio: 'inherit'
        } );

    if ( spawnResult.status !== 0 )
    {
        console.log( ('node error code ' + spawnResult.status.toString()).red.bold );
        if ( exitOnError )
        {
            process.exit( spawnResult.status );
        }
    }

    return spawnResult;
}

function db2pdl() {
    runNodeSync( [
        path.join( process.env.PDL_BIN_PATH, 'pdl-node/db2pdl' ),
        '--run',
        '--exit'
    ], true );
}

function build() {
    return new Promise( resolve => {
        preBuildSteps()
            .then( () => {
                compilePdlSources();
                webpackRun();
                postBuildCopy()
                    .then( resolve );
            } )
    } );
}

function setRebuildFlag() {
    doRebuild = true;
}

function compilePdlSources() {

    const nodeParams = [
        path.join( process.env.PDL_BIN_PATH, "pdl-node" )
    ];

    if ( doRebuild )
    {
        nodeParams.push( '--rebuild' );
    }

    runNodeSync( nodeParams, true );

}

function webpackRun() {
    runNodeSync( [
        'node_modules/webpack/bin/webpack.js'
    ], true );
}

/**
 *
 * @param dest
 */
function tryCleanDest( dest ) {

    if ( doCleanDest )
    {
        rimraf.sync( dest );
        console.log( dest + " cleaned..." );
    }

}

function rebuild() {
    return new Promise( resolve => {
        setRebuildFlag();
        preBuildSteps().then( () => {
            compilePdlSources();
            postRebuild().then( resolve );
        } );
    } )

}

task( 'default', build );

task( 'rebuild', rebuild );
task( 'rebuild-all', () => {
    return new Promise( resolve => {
        db2pdl();
        rebuild()
            .then( () => resolve() );
    } )
} );

task( 'watch.pdl', () => {
    // noinspection JSCheckFunctionSignatures
    gulp.watch( 'src/**/*.pdl', build );
} );

