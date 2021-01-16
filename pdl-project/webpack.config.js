const path = require( 'path' );

const dotenv = require( 'dotenv' );

dotenv.config();

const outputDir = process.env.PDL_OUTPUT;

const pdlConfig = require( './pdl.config' );
const ProjectName = pdlConfig.project;

const entry = {};
entry[ ProjectName + 'PdlLib' ] = ProjectName + 'Pdl.js';

module.exports = {

    entry: entry,
    devtool: "source-map",

    output: {
        filename: path.join( outputDir, 'webpack/bundles/[name].js' ),
        library: "[name]",
        libraryTarget: "var"
    },

    externals: {
        "com.namespace": 'com'
    },

    resolve: {
        modules: [
            path.resolve( 'node_modules' ),
            path.resolve( path.join( outputDir, 'js' ) ),
            path.resolve( '../resources/js' )
        ]
    },
    watch: false,
    watchOptions: {
        aggregateTimeout: 300,
        poll: 1000
    }

};
