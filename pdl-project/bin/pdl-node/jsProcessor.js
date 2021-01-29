const fs = require( 'fs-extra' );
const path = require( 'path' );
const js2ts = require( './js2ts' );
const jsdocParser = require( './jsdocParser' );
const pdlUtils = require( './utils' );

const configs = require( './config' );
const templates = require( './templates' );
const stringUtils = require( 'string' );
//const util = require( 'util' );
const stringifyObject = require( 'stringify-object' );

/**
 *
 * @type {PdlConfig}
 */
let pdlConfig = new configs.PdlConfig();

/**
 *
 * @type {JsConfig}
 */
let jsConfig = new configs.JsConfig();

let globalIndexClasses = [];
let usedInnerNamespaces = {};
let namespaceTree = {};
let namespaceFiles = {};

/**
 *
 * @type {TemplateUtils}
 */
let templateUtils = null;

/**
 *
 */
function generateGlobalIndexFile() {

    let indexSource = templateUtils.render( jsConfig.globalIndex.template, {
        namespaceFiles: namespaceFiles,
        globalIndexClasses: globalIndexClasses
    } );
    fs.ensureDirSync( jsConfig.globalIndex.outputDir );

    outputSourceCode( jsConfig.globalIndex.outputDir, indexSource, jsConfig.globalIndex.filename );

}

/**
 *
 * @param dir
 * @param sourceCode
 * @param filename
 */
function outputSourceCode( dir, sourceCode, filename ) {
    const file = path.join( dir, filename );
    if ( fs.existsSync( file ) )
    {
        fs.unlinkSync( file );
    }

    fs.appendFileSync( file, sourceCode );
}

/**
 *
 * @param {string} dir
 * @param {ClassData[]} classes
 */
function generateIndexFile( dir, classes ) {

    const indexSource = templateUtils.render( jsConfig.index.template, {
        classes: classes
    } );

    outputSourceCode( dir, indexSource, jsConfig.index.filename );

}

/**
 *
 * @param {string} dir
 * @param {string[]} filenames
 *
 * @returns {ClassData[]}
 */
function generateDirClasses( dir, filenames ) {
    const result = [];
    filenames.forEach( function( filename ) {
        const classData = jsdocParser.parseClass( path.join( dir, filename ) );
        if ( classData.isValidClass() )
        {
            result.push( classData );
        }
    } );

    return result;

}

/**
 *
 * @param {string} dir
 * @returns {ClassData[]}
 */
function processDir( dir ) {
    const filesOrDirs = fs.readdirSync( dir );
    let dirs = [];
    let files = [];
    let result = [];

    filesOrDirs.forEach( function( fileOrDir ) {
        if ( fs.statSync( path.join( dir, fileOrDir ) ).isDirectory() )
        {
            if ( fileOrDir !== '.' && fileOrDir !== '..' )
            {
                dirs.push( path.join( dir, fileOrDir ) );
            }
        }
        else
        {
            files.push( fileOrDir );
        }
    } );

    if ( files.length > 0 )
    {
        /**
         *
         * @type {ClassData[]}
         */
        result = generateDirClasses( dir, files );

        if ( result.length > 0 )
        {
            result[ result.length - 1 ].last = true;
            if ( jsConfig.index.enabled )
            {
                generateIndexFile( dir, result );
            }
        }

        if ( jsConfig.typescript.generate )
        {
            js2ts.generate( dir, files );
        }
    }

    if ( dirs.length )
    {
        processDirs( dirs );
    }

    return result;

}

/**
 *
 * @param {String }namespace
 *
 * back namespaces backwards until it has never been used
 * if it is all used then count
 *
 */
function getGlobalIndexNamespace( namespace ) {

    const namespaceParts = namespace.split( '.' );

    let index = jsConfig.globalIndex.namespaces.depth || namespaceParts.length;
    index = Math.min( namespaceParts.length, index );

    let result = '';

    do
    {
        --index;
        result = stringUtils( namespaceParts.pop() ).capitalize() + result;
    }
    while ( index > 0 );

    if ( usedInnerNamespaces.hasOwnProperty( result ) )
    {
        let count = 0;
        const globalIndexNamespace = result;
        while ( usedInnerNamespaces.hasOwnProperty( result ) )
        {
            ++count;
            result = globalIndexNamespace + count.toString();
        }

    }

    usedInnerNamespaces[ result ] = true;

    return result;
}

/**
 *
 * @param {string} dir
 * @param {ClassData[]} classes
 */
function addToGlobalIndex( dir, classes ) {

    const relativeDir = path.relative( jsConfig.globalIndex.outputDir, dir )
        .split( path.sep )
        .join( path.posix.sep );

    if ( relativeDir )
    {
        globalIndexClasses.push( {
            namespace: getGlobalIndexNamespace( classes[ 0 ].classNode.namespace ),
            path: relativeDir,
            classes: classes
        } );
    }

}

/**
 *
 * @param {string} fullNamespace
 */
function addToNamespaces( fullNamespace ) {
    const parts = fullNamespace.split( '.' );
    let root = namespaceTree;
    parts.forEach( function( namespace ) {
        if ( !root.hasOwnProperty( namespace ) )
        {
            root[ namespace ] = {};
        }
        root = root[ namespace ];
    } );
}

/**
 *
 * @param dirs
 */
function processDirs( dirs ) {
    dirs.forEach( function( dir ) {
        if ( fs.existsSync( dir ) )
        {
            const classes = processDir( dir );

            if ( classes.length > 0 )
            {
                if ( jsConfig.namespaces.enabled )
                {
                    addToNamespaces( classes[ 0 ].classNode.namespace );
                }

                if ( generateGlobalIndexEnabled() )
                {
                    addToGlobalIndex( dir, classes );
                }
            }
        }
    } );
}

/**
 *
 * @returns {boolean}
 */
function generateGlobalIndexEnabled() {
    const result = jsConfig.index.enabled && jsConfig.globalIndex.enabled;
    return result;
}

/**
 *
 * @param root
 */
function getNamespaceFilename( root ) {
    const filename = jsConfig.namespaces.filename.replace( "[root]", root );

    const result = path.join( jsConfig.namespaces.outputDir, filename );

    return result;
}

/**
 *
 * @param root
 * @param {string} source
 */
function outputNamespaceFile( root, source ) {
    const filename = getNamespaceFilename( root );
    fs.appendFileSync( filename, source );
}

/**
 *
 */
function generateNamespaceFiles() {
    namespaceFiles = [];
    let root;

    addToNamespaces( 'com.mh.ds.infrastructure.data' );
    addToNamespaces( 'com.mh.ds.infrastructure.languages.js' );

    for ( root in namespaceTree )
    {
        const filename = getNamespaceFilename( root );

        namespaceFiles.push( {
            root: root,
            filename: path.parse( filename ).name
        } );

        if ( fs.existsSync( filename ) )
        {
            fs.unlinkSync( filename );
        }
    }

    for ( root in namespaceTree )
    {
        if ( namespaceTree.hasOwnProperty( root ) )
        {

            const source = templateUtils.render( jsConfig.namespaces.template, {
                root: root,
                tree: stringifyObject( namespaceTree[ root ], {
                    indent: '    '
                } )
            } );

            outputNamespaceFile( root, source );
        }
    }
}

/**
 *
 * @param config
 */
function init( config ) {
    pdlConfig = config;
    jsConfig = pdlConfig.js;

    templateUtils = new templates.TemplateUtils( pdlConfig,
        pdlUtils.getTemplatesDir( jsConfig.templatesDir ), 'js' );

    js2ts.init( config );
}

/**
 * @params {PdlConfig} config
 */
function run( config ) {

    init( config );

    processDirs( jsConfig.dirs );

    if ( jsConfig.namespaces.enabled )
    {
        generateNamespaceFiles();
    }

    if ( generateGlobalIndexEnabled() )
    {
        generateGlobalIndexFile();
    }

    js2ts.end();

}

module.exports = {
    run: run
};
