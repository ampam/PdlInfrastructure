const fs = require( 'fs-extra' );
const path = require( 'path' );

/**
 *
 * @constructor {FileTimes}
 * @param {string} filename
 */
function FileTimes( filename ) {

    let modifiedFileTimes = {};

    /**
     *
     * @param file
     */
    function getModifiedTimeWhenCompiled( file ) {

        const result = modifiedFileTimes.hasOwnProperty( file )
            ? modifiedFileTimes[ file ]
            : 0;
        return result;

    }

    /**
     *
     * @param file
     * @return {boolean}
     */
    this.isFileModified = function( file ) {

        const currentTime = fs.statSync( file ).mtime.getTime();
        const result = currentTime !== getModifiedTimeWhenCompiled( file );

        return result;
    };

    /**
     *
     */
    this.read = function() {
        if ( fs.existsSync( filename ) )
        {
            try
            {
                modifiedFileTimes = JSON.parse( fs.readFileSync( filename, 'utf8' ) );
            }
            catch ( Error )
            {
                console.log( "Error reading filetime: " + filename );
            }
        }
    };

    /**
     *
     * @param file
     */
    this.addFile = function( file ) {
        modifiedFileTimes[ file ] = fs.statSync( file ).mtime.getTime();
    };

    /**
     *
     */
    this.write = function() {
        const parts = path.parse( filename );
        fs.ensureDirSync( parts.dir );
        fs.writeFileSync( filename, JSON.stringify( modifiedFileTimes ) );
    };

}

module.exports = {

    createFileTimes:
        /**
         *
         * @param filename
         * @return {FileTimes}
         */
        function( filename ) {
            let result = new FileTimes( filename );
            return result;
        }
};
