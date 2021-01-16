const extend = require( "extend" );

/**
 *
 * @param config
 * @param sectionName
 * @returns {null}
 */
function getSectionByName( config, sectionName )
{
    let result = null;

    config.sections.forEach( section => {
        if ( section.name === sectionName )
        {
            result = section;
        }
    });

    return result;

}

/**
 *
 * @param config
 * @returns {*|{rebuild: boolean, outputDir: string | undefined, src: [string], db2Pdl: {cs: {emit: boolean}, outputDir: string | undefined, templatesDir: string, excludedTables: [], excludedColumns: [], php: {emitHelpers: boolean, attributes: {dbId: string, columnName: string}}, pdl: {entitiesNamespace: string, db2PdlSourceDest: string, attributes: {dbId: string, columnName: string}, useNamespaces: []}, connection: {password: string | undefined, database: string | undefined, port: string | undefined, host: string | undefined, type: string, user: string | undefined}, enabled: boolean, verbose: boolean, ts: {outputFile: string, emit: boolean}}, db2PdlSourceDest: string, companyName: string, templates: {name: string, dir: string}, profiles: {phpJs: {configFile: string, templates: {dir: string}}, dbFiles: {outputDir: string | undefined, src: [], configFile: string, templates: {dir: string}}}, project: string, js: {globalIndex: {template: string, outputDir: string, filename: string, enabled: boolean, namespaces: {depth: number}}, templatesDir: string, index: {template: string, filename: string, enabled: boolean}, dirs: [string], typescript: {generate: boolean}, namespaces: {template: string, outputDir: string, filename: string, enabled: boolean}}, version: string, sections: [{name: string, files: {dbFilesExclude: [], dbFiles: [string]}}, {name: string, files: {phpJsExclude: [string], phpJs: [string]}}], verbose: boolean, compilerPath: string | undefined, tempDir: string}}
 */
function mergeConfig( config ) {
    let commonConfig = require( './common.pdl.config' );

    let result = extend( true, {}, commonConfig, config );

    const projectName = result.project;
    result.js.globalIndex.filename = `${projectName}Pdl.js`

    const db2PdlSourceDest = result.db2PdlSourceDest;

    const dbSection = getSectionByName( result, "DbFiles");
    dbSection.files.dbFiles = [ db2PdlSourceDest + '/*.pdl' ];

    const phpJsSection = getSectionByName( result, "Php And Js");
    phpJsSection.files.phpJsExclude = dbSection.files.dbFiles;

    result.db2Pdl.pdl.db2PdlSourceDest = db2PdlSourceDest;
    result.db2Pdl.pdl.entitiesNamespace = db2PdlSourceDest.split( '/').join( '.' );

    return result;
}

module.exports = mergeConfig;
