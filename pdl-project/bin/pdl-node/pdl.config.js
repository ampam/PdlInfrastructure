var path = require( 'path' );
var glob = require('glob');

var developmentRoot = process.env.PAM_DEV || path.join( process.env.HOME, 'development' );

module.exports = {

    companyName: 'PlusAMedia',
    project: 'Pdl Project',
    version: '1.0.0',

    /**
     * Leave empty for default.
     * Use it when the compiler path is not in the PATH ENV variable
     */
    compilerPath: path.join( developmentRoot, 'pdl/bin' ),

    rebuild: false,
    verbose: false,
    src: [
        '.',
        'src'
    ],

    outputDir: 'output',
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

        globalIndex: {
            enabled: true,
            filename: 'pamPdl.js',
            template:'global-index',
            outputDir: 'output/js',
            namespaces: {
                depth: 2
            }
        },

        namespaces: {
            enabled: true,
            filename: "[root].namespace.js",
            outputDir: 'output/js',
            template: 'namespace'
        },

        templatesDir: 'default',

        dirs: [
            'output/js'
        ],

        typescript: {
            generate: true,
            generateIndex: true,
            indexFilename: 'index.ts',
            barrelFilename: 'barrel.ts',
            classTemplate: 'singleClass',
            indexTemplate: 'index',
            barrelTemplate: 'barrel',
            outputDir: 'output/js2ts'
        }

    },

    profiles: {
        dbFiles: {
            configFile: 'config/pdl.config.db.json',

            /** overrides global - optional */
            src: [],

            /** overrides global - optional */
            outputDir: 'output',

            /** overrides global - optional */
            templates: {
                dir: 'pdl-templates/db'
            }
        },
        phpAndJs: {
            configFile: 'config/pdl.php.and.js.json',
            templates: {
                dir: 'pdl-templates'
            }
        },
        default: {
            configFile: 'config/pdl.config.default.json',
            templates: {
                dir: 'pdl-templates'
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
                ],
                dbFilesExclude: [
                ]
            }
        },
        {
            name: 'Main',
            files: {
                default: [
                    '**/*.pdl'
                ]
            }

        }
        ,
        {
            name: 'Php And Js',
            files: {
                phpAndJs:[
                    'some-dir/**/*.pdl'
                ]
            }

        }
    ],

    db2Pdl: {
        enabled: true,
        verbose: false,
        connection: {
            type: 'mysql',
            host: "localhost",
            port: 3306,
            user: 'root',
            password: undefined,
            database: 'my_db'
        },

        outputDir: 'db2pdl/output',
        templatesDir: 'default',
        ts: {
            emit: true,
            outputFile: 'DbTypes'
        },
        cs: {
            emit: true
        },
        php: {
            emitHelpers: true,
            entitiesNamespace: 'Com\\Mh\\Ds\\Domain\\Data',
            useNamespaces: [
            ],
            attributes: {
                dbId: "[ 'IsDbId' => [] ]",
                columnName: "[ 'ColumnName' => [ 'default1' => '{$column_name}' ] ]"
            }
        },
        pdl: {
            attributes: {
                dbId: '[com.mh.ds.infrastructure.data.attributes.IsDbId]',
                columnName: '[com.mh.ds.infrastructure.data.attributes.ColumnName("{$column_name}")]'
            }
        },
        excludedTables: [],
        excludedColumns: []
    }
};
