const extend = require( "extend" );


const commonConfig = require( './common.pdl.config' );

/**
 *
 * @param config
 * @param sectionName
 * @returns {null}
 */
function getSectionByName( config, sectionName ) {
    let result = null;

    config.sections.forEach( section => {
        if ( section.name === sectionName )
        {
            result = section;
        }
    } );

    return result;

}

function getUserGlobPatterns( userSections, finalConfig  ) {

    let result = [];
    userSections.forEach( ( section ) => {

        for ( const profileName in section.files )
        {
            if ( section.files.hasOwnProperty( profileName ) )
            {
                if ( !profileName.endsWith( 'Exclude' ) )
                {
                    if ( finalConfig.profiles.hasOwnProperty( profileName ) )
                    {
                        result = result.concat( section.files[ profileName ] );
                    }
                    else
                    {
                        console.error( "Invalid profile: " + profileName );
                    }
                }
            }
        }
    } );
    return result;
}

function mergeSections( finalConfig, userConfig ) {

    finalConfig.sections = commonConfig.sections.concat( userConfig.sections );
    let excludePatterns =  getUserGlobPatterns( userConfig.sections || [], finalConfig );

    const dbSection = getSectionByName( finalConfig, "DbFiles" );
    dbSection.files.dbFilesExclude = dbSection.files.dbFilesExclude.concat( excludePatterns );

    const db2PdlSourceDest = userConfig.db2PdlSourceDest;
    dbSection.files.dbFiles = [db2PdlSourceDest + '/*.pdl'];

    const phpJsSection = getSectionByName( finalConfig, "Php And Js" );
    phpJsSection.files.phpJsExclude = phpJsSection.files.phpJsExclude
        .concat( excludePatterns )
        .concat( dbSection.files.dbFiles );

}

/**
 *
 * @param userConfig
 * @returns {*|{rebuild: boolean, outputDir: string | undefined, src: [string], db2Pdl: {cs: {emit: boolean}, outputDir: string | undefined, templatesDir: string, excludedTables: [], excludedColumns: [], php: {emitHelpers: boolean, attributes: {dbId: string, columnName: string}}, pdl: {entitiesNamespace: string, db2PdlSourceDest: string, attributes: {dbId: string, columnName: string}, useNamespaces: []}, connection: {password: string | undefined, database: string | undefined, port: string | undefined, host: string | undefined, type: string, user: string | undefined}, enabled: boolean, verbose: boolean, ts: {outputFile: string, emit: boolean}}, db2PdlSourceDest: string, companyName: string, templates: {name: string, dir: string}, profiles: {phpJs: {configFile: string, templates: {dir: string}}, dbFiles: {outputDir: string | undefined, src: [], configFile: string, templates: {dir: string}}}, project: string, js: {globalIndex: {template: string, outputDir: string, filename: string, enabled: boolean, namespaces: {depth: number}}, templatesDir: string, index: {template: string, filename: string, enabled: boolean}, dirs: [string], typescript: {generate: boolean}, namespaces: {template: string, outputDir: string, filename: string, enabled: boolean}}, version: string, sections: [{name: string, files: {dbFilesExclude: [], dbFiles: [string]}}, {name: string, files: {phpJsExclude: [string], phpJs: [string]}}], verbose: boolean, compilerPath: string | undefined, tempDir: string}}
 */
function mergeConfig( userConfig ) {


    let result = extend( true, {}, commonConfig, userConfig );

    const projectName = result.project;
    result.js.globalIndex.filename = `${projectName}Pdl.js`

    mergeSections( result, userConfig );

    const db2PdlSourceDest = result.db2PdlSourceDest;
    result.db2Pdl.pdl.db2PdlSourceDest = db2PdlSourceDest;
    result.db2Pdl.pdl.entitiesNamespace = db2PdlSourceDest.split( '/' ).join( '.' );

    return result;
}

module.exports = mergeConfig;
