const extend = require( 'extend' );

/**
 *
 */
com.mh.ds.infrastructure.languages.js.Inheritance = function() {

    /**
     *
     * @param namespace
     * @param theClass
     * @param fullBaseClass
     */
    this.inherit = ( namespace, theClass, fullBaseClass ) => {
        if ( fullBaseClass )
        {
            for ( const propertyName in fullBaseClass )
            {
                //noinspection JSUnfilteredForInLoop
                if ( !(namespace[ theClass ][ propertyName ] ) )
                {
                    //noinspection JSUnfilteredForInLoop
                    namespace[ theClass ].prototype[ propertyName ] = fullBaseClass[ propertyName ];
                }
            }

        }
    };

    /**
     *
     * @param obj
     * @param baseObj
     * @return {Object}
     */
    this.inheritWithExtend = ( obj, baseObj ) => {

        const result = extend( true, {}, baseObj, obj );

        if ( result.hasOwnProperty('inherited') )
        {
            result.inherited = baseObj;
        }

        if ( typeof obj['setInherited'] === 'function' )
        {
            obj['setInherited']( baseObj );
        }

        return result;
    };

};

module.exports = new com.mh.ds.infrastructure.languages.js.Inheritance();

