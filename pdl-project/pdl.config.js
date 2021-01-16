const path = require( 'path' );
const dotenv = require( 'dotenv' );
dotenv.config();

const PdlCompilerTemplatesDir = 'pdl-templates';

const OrganizartionName = "My Organization";
const ProjectName = 'My Project';
const Db2PdlSourceDest = 'my-organization/my-project/domain/data';

module.exports = {

    companyName: OrganizartionName,
    project: ProjectName,
    version: '1.0.0',

    compilerPath: process.env.PDL_BIN_PATH,

    rebuild: false,
    verbose: false,
    src: [
        '.',
        'src'
    ],

    outputDir: process.env.PDL_OUTPUT,
    tempDir: 'temp',

    templates: {
        dir: '',
        name: 'classTemplate1'
    },

    js: {
        index: {
            enabled: true,
            filename: 'index.js',
            template: 'index'
        },

        namespaces: {
            enabled: true,
            filename: "[root].namespace.js",
            outputDir: path.join( process.env.PDL_OUTPUT, 'js' ),
            template: 'namespace'
        },

        globalIndex: {
            enabled: true,
            filename: ProjectName + 'Pdl.js',
            template: 'global-index',
            outputDir: path.join( process.env.PDL_OUTPUT, 'js' ),
            namespaces: {
                depth: 2
            }
        },

        templatesDir: 'default',

        dirs: [
            path.join( process.env.PDL_OUTPUT, 'js' ),
        ],

        typescript: {
            generate: false,
            // generateIndex: true,
            // indexFilename: 'index.ts',
            // barrelFilename: 'barrel.ts',
            // classTemplate: 'singleClass',
            // indexTemplate: 'index',
            // barrelTemplate: 'barrel',
            // outputDir: 'output/js2ts'
        }

    },

    profiles: {
        dbFiles: {
            configFile: 'config/pdl.config.db.json',

            /** overrides global - optional */
            src: [],

            /** overrides global - optional */
            outputDir: process.env.PDL_OUTPUT,

            /** overrides global - optional */
            templates: {
                dir: path.join( PdlCompilerTemplatesDir, 'db' )
            }
        },
        phpJs: {
            configFile: 'config/pdl.php.js.json',
            templates: {
                dir: PdlCompilerTemplatesDir
            }
        },
        default: {
            configFile: 'config/pdl.config.default.json',
            templates: {
                dir: PdlCompilerTemplatesDir
            }
        }
    },

    sections: [
        {
            name: 'Db Files',

            /** overrides profile - optional */
            //outputDir: '',

            /** overrides profile - optional */
            // src: [
            //     ''
            // ],

            files: {
                dbFiles: [
                    Db2PdlSourceDest + '/*.pdl'
                ],
                dbFilesExclude: []
            }
        },
        {
            name: 'Php And Js',
            files: {
                phpJs: [
                    '**/*.pdl',
                ],
                phpJsExclude: [
                    Db2PdlSourceDest + '/*.pdl'
                ]
            }

        },
    ],

    db2Pdl: {
        enabled: true,
        verbose: false,
        connection: {
            type: 'mysql',
            host: process.env.DB_HOST,
            port: process.env.DB_PORT,
            user: process.env.DB_USERNAME,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_DATABASE
        },
        outputDir: process.env.PDL_DB2PDL_OUTPUT,
        templatesDir: 'default',
        ts: {
            emit: false,
            outputFile: 'DbTypes'
        },
        cs: {
            emit: false
        },
        php: {
            emitHelpers: true,
            attributes: {
                dbId: "[ 'IsDbId' => [] ]",
                columnName: "[ 'ColumnName' => [ 'default1' => '{$column_name}' ] ]"
            }
        },
        pdl: {
            db2PdlSourceDest: Db2PdlSourceDest,
            entitiesNamespace: Db2PdlSourceDest.split( '/').join( '.' ),
            useNamespaces: [],
            attributes: {
                dbId: '[com.mh.ds.infrastructure.data.attributes.IsDbId]',
                columnName: '[com.mh.ds.infrastructure.data.attributes.ColumnName("{$column_name}")]'
            }
        },
        excludedTables: [],
        excludedColumns: []
    }
};
