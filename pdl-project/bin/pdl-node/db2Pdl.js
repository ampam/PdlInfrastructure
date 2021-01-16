const fs = require( 'fs-extra' );
const path = require( 'path' );
const mysql = require( 'mysql2' );
const changeCase = require( 'change-case' );
const pluralize = require( 'pluralize' );
//let handlebars = require( 'handlebars' );
const numeral = require( 'numeral' );
const templates = require( './templates' );
require( 'colors' );

const pdlUtils = require( './utils' );
const verboseLog = pdlUtils.verboseLog;

const mySqlTypes = require( './mysqlTypes' );


/**
 *
 * @type {TemplateUtils}
 */
let templateUtils = null;

const cwd = process.cwd();
let connection = null;
let tsBlocks = [];

let exitWhenDone = false;
let totalTables = 0;

let totalFiles = 0;
let totalLines = 0;
let totalSize = 0;

let configFile = path.join( cwd, 'pdl.config.js' );
let mySqlConfig = null;
let defaultEntitiesNamespace = 'com.mh.ds.domain.data';
let phpDefaultEntitiesNamespace = pdlUtils.namespace2php( defaultEntitiesNamespace );


let templatesDir = 'default';

let doRun = false;
processCommandLine();

if ( doRun )
{
    run();
}

/**
 *
 */
function processCommandLine() {
    for ( let i = 2; i < process.argv.length; i++ )
    {
        let argument = process.argv[ i ];
        let parts = argument.split( '=' );

        let option = parts[ 0 ].trim();

        let value = parts.length > 1
            ? parts[ 1 ].trim()
            : '';

        switch ( option )
        {
            case '--run':
                doRun = true;
                break;
            case '--config':
                configFile = value;
                break;

            case '--exit':
                exitWhenDone = true;
                break;
        }
    }
}

/**
 *
 * @param exitCode
 * @param message
 */
function exit( exitCode, message ) {

    if ( message )
    {
        console.log( message )
    }
    process.exit( exitCode );
}

/**
 *
 */
function cleanOutput() {
    console.log( 'Cleaning output...' );
    pdlUtils.cleanDir( mySqlConfig.outputDir );
}

/**
 *
 * @param {String} mySqlType
 */
function clearSqlType( mySqlType ) {

    let result = mySqlType.split( '(' ).shift();
    return result;
}

/**
 *
 * @param language
 * @param cleanSqlType
 * @return {string}
 */
function mySql2LanguageType( language, cleanSqlType ) {

    let result = mySqlTypes[ language ].hasOwnProperty( cleanSqlType )
        ? mySqlTypes[ language ][ cleanSqlType ]
        : 'string';

    return result;
}

/**
 *
 * @param fieldName
 * @return {boolean}
 */
function cantConvertBackAndForth( fieldName ) {
    let matches = fieldName.match( /\d+/g );

    let result = matches !== null;
    return result;
}

/**
 *
 * @param fieldInfo
 * @param attribute
 */
function addPhpFieldAttribute( fieldInfo, attribute ) {
    fieldInfo.phpAttributes = fieldInfo.phpAttributes || [];
    fieldInfo.phpAttributes.push( generateAttribute( fieldInfo, mySqlConfig.php.attributes[ attribute ] ) );
}

/**
 *
 * @param attribute
 * @param fieldInfo
 */
function generateAttribute( fieldInfo, attribute ) {

    let result = attribute
        .replace( '{$columnName}', fieldInfo.camelCase )
        .replace( '{$column_name}', fieldInfo.snakeCase );

    return result;
}

/**
 *
 * @param fieldInfo
 * @param attribute
 */
function addPdlFieldAttribute( fieldInfo, attribute ) {
    fieldInfo.pdlAttributes = fieldInfo.pdlAttributes || [];
    fieldInfo.pdlAttributes.push( generateAttribute( fieldInfo, mySqlConfig.pdl.attributes[ attribute ] ) );
}

/**
 *
 * @param fieldName
 */
function isExcluded( fieldName ) {

    let result = mySqlConfig.excludedColumns.indexOf( fieldName ) >= 0;
    return result;
}

/**
 *
 * @param {*} textRow
 * @param tableData
 */
function generateFieldInfo( textRow, tableData ) {

    // noinspection JSUnresolvedVariable
    let snakeCase = textRow.Field;
    let camelCase = changeCase.camelCase( snakeCase, '', true );
    let pascalCase = changeCase.pascalCase( camelCase, '', true );
    // noinspection JSUnresolvedVariable
    let cleanSqlType = clearSqlType( textRow.Type );

    let result = {
        fieldName: camelCase,
        camelCase: camelCase,
        original: snakeCase,
        snakeCase: snakeCase,
        pascalCase: pascalCase,
        tsType: mySql2LanguageType( 'TypeScript', cleanSqlType ),
        dbType: cleanSqlType,
        phpType: mySql2LanguageType( 'Php', cleanSqlType ),
        pdlType: mySql2LanguageType( 'Pdl', cleanSqlType )
    };

    // noinspection JSUnresolvedVariable
    if ( textRow.Key === 'PRI' )
    {
        tableData.primaryKeyPascalCase = pascalCase;
        tableData.primaryKeyCamelCase = camelCase;
        addPdlFieldAttribute( result, 'dbId' );
        addPhpFieldAttribute( result, 'dbId' );
    }

    if ( cantConvertBackAndForth( snakeCase ) )
    {
        addPdlFieldAttribute( result, 'columnName' );
        addPhpFieldAttribute( result, 'columnName' );
    }

    if ( result.hasOwnProperty( 'phpAttributes' ) )
    {
        result.phpAttributes = result.phpAttributes.join( '\n' );
        result.pdlAttributes = result.pdlAttributes.join( '\n' );
    }

    return result;

}

/**
 *
 * @param tableName
 * @param results
 */
function generateTableData( tableName, results ) {
    let name = pluralize.singular( changeCase.pascalCase( tableName ) );
    let rowClass = name + "Row";

    let result = {
        name: name,
        tableName: tableName,
        dbName: mySqlConfig.connection.database,
        pdlRowClass: rowClass,
        rowClass: rowClass,
        rowSetClass: rowClass + 'Set',
        columnsDefinitionClass: name + 'Columns',
        whereClass: name + 'Where',
        orderByClass: name + 'OrderBy',
        fieldListClass: name + 'FieldList',
        columnsListTraits: name + 'ColumnsTraits',
        csharpRowSetClass: rowClass + 'Set',
        phpUseNamespaces: defaultEntitiesNamespace,
        phpEntitiesNamespace: phpDefaultEntitiesNamespace,
        pdlEntitiesNamespace: mySqlConfig.pdl.entitiesNamespace,
        pdlUseNamespaces: mySqlConfig.pdl.use
    };

    let fieldsInfo = [];

    results.forEach(
        /**
         * @param {*} textRow
         */
        function( textRow ) {
            // noinspection JSUnresolvedVariable
            if ( !isExcluded( textRow.Field ) )
            {
                fieldsInfo.push( generateFieldInfo( textRow, result ) );
            }

        } );


    fieldsInfo.sort( function( left, right ) {
        let result = left.original.localeCompare( right.original );
        return result;
    });

    result.fieldsInfo = fieldsInfo;
    return result;

}

/**
 *
 * @param {string} sourceCode
 * @param {string} filename
 * @param {string} type
 */
function outputCode( sourceCode, filename, type ) {

    let dir = type
        ? path.join( mySqlConfig.outputDir, type )
        : mySqlConfig.outputDir;

    filename = type
        ? filename + '.' + type
        : filename;

    fs.ensureDirSync( dir );

    let outputFileName = path.join( dir, filename );

    if ( fs.existsSync( outputFileName ) )
    {
        fs.unlinkSync( outputFileName );
    }
    fs.writeFileSync( outputFileName, sourceCode );

    verboseLog( outputFileName + ' generated.' );

    totalFiles++;
    totalSize += fs.statSync( outputFileName ).size;
    totalLines += sourceCode.match( /\n/g ).length;
}

/**
 *
 * @param tableData
 * @param type
 * @param templateName
 */
function generateFile( tableData, type, templateName ) {
    let sourceCode = templateUtils.render( path.join( type, templateName ), tableData );
    outputCode( sourceCode, tableData[ templateName ], type );
}

/**
 *
 * @param tableData
 */
function generateClasses( tableData ) {

    generateFile( tableData, 'pdl', 'pdlRowClass' );

    if ( mySqlConfig.php.emitHelpers )
    {
        generateFile( tableData, 'php', 'rowClass' );
        generateFile( tableData, 'php', 'columnsDefinitionClass' );
        generateFile( tableData, 'php', 'whereClass' );
        generateFile( tableData, 'php', 'orderByClass' );
        generateFile( tableData, 'php', 'columnsListTraits' );
        generateFile( tableData, 'php', 'fieldListClass' );
    }

    if ( mySqlConfig.ts.emit )
    {
        tsBlocks.push( templateUtils.render( path.join( 'ts', 'dbBlock' ), tableData ) );
    }

    if ( mySqlConfig.cs.emit )
    {
        generateFile( tableData, 'cs', 'csharpRowSetClass' );
    }
}

/**
 *
 */
function finish() {

    if ( mySqlConfig.ts.emit )
    {
        let tsSourceCode = templateUtils.render(
            path.join( 'ts', 'dbModule' ), {
                source: tsBlocks.join( '\n' )
            }
        );
        outputCode( tsSourceCode, mySqlConfig.ts.outputFile, 'ts' );
    }

    console.log( 'Stats: '.bold + numeral( totalFiles ).format( '0,0' ).green.bold +
        ' files generated, ' + numeral( totalLines ).format( '0,0' ).green.bold +
        ' lines generated, ' + numeral( totalSize ).format( '0.0b' ).green.bold );

    if ( exitWhenDone )
    {
        process.exit();
    }
}

/**
 *
 * @param tableName
 */
function processTable( tableName ) {
    connection.query( "SHOW COLUMNS FROM " + tableName, function( error, results/*, fields*/ ) {
        if ( !error )
        {
            let tableData = generateTableData( tableName, results );
            generateClasses( tableData );
            decrementTablesCount();
        }
        else
        {
            exit( 1, error );
        }
    } );
}

/**
 *
 */
function decrementTablesCount() {
    --totalTables;
    if ( totalTables <= 0 )
    {
        finish();
    }

}

/**
 *
 * @param results
 * @param columnName
 */
function processTableList( results, columnName ) {

    totalTables = results.length;

    results.forEach(
        /**
         * @param {*} textRow
         */
        function( textRow ) {
            let tableName = textRow[ columnName ];
            if ( mySqlConfig.excludedTables.indexOf( tableName ) < 0 )
            {
                processTable( tableName );
            }
            else
            {
                decrementTablesCount();
            }
        } );

}

/**
 *
 */
function generate() {
    cleanOutput();

    connection = new mysql.createConnection( mySqlConfig.connection );

    connection.query( "SHOW TABLES FROM " + mySqlConfig.connection.database,
        function( error, results, fields ) {
            if ( !error )
            {
                processTableList( results, fields[ 0 ].name );
            }
            else
            {
                exit( 1, error );
            }
        } );
}

/**
 *
 */
function processConfig() {
    let pdlConfig = require( configFile );
    mySqlConfig = pdlConfig.db2Pdl;

    defaultEntitiesNamespace = mySqlConfig.pdl.entitiesNamespace || 'com.mh.ds.domain.data';
    phpDefaultEntitiesNamespace = pdlUtils.namespace2php( defaultEntitiesNamespace );

    pdlUtils.setVerbose( pdlConfig.verbose || mySqlConfig.verbose || false );
    templatesDir = pdlUtils.getTemplatesDir( mySqlConfig.templatesDir );

    templateUtils = new templates.TemplateUtils(
        pdlConfig,
        pdlUtils.getTemplatesDir( mySqlConfig.templatesDir ),
        '' );
}

/**
 *
 */
function run() {

    processConfig();
    if ( mySqlConfig.enabled )
    {
        generate();
    }
}

module.exports = {
    run: run,
    runAndExit: function() {
        exitWhenDone = true;
        run();
    }
};
