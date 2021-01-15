const extend = require( 'extend' );

/**
 *
 * @param namespace
 * @param theClass
 * @param fullBaseClass
 */
function inherit( namespace, theClass, fullBaseClass ) {
    inheritNoNs( namespace[ theClass ], fullBaseClass );

}

/**
 *

 * @param theClass
 * @param fullBaseClass
 */
function inheritNoNs( theClass, fullBaseClass ) {
    if ( fullBaseClass )
    {
        for ( const propertyName in fullBaseClass )
        {
            //noinspection JSUnfilteredForInLoop
            if ( !(theClass[ propertyName ]) )
            {
                //noinspection JSUnfilteredForInLoop
                theClass.prototype[ propertyName ] = fullBaseClass[ propertyName ];
            }
        }

    }
}

/**
 *
 * @param obj
 * @param baseObj
 * @return {Object}
 */
function inheritWithExtend( obj, baseObj ) {

    const result = extend( true, {}, baseObj, obj );

    if ( result.hasOwnProperty( 'inherited' ) )
    {
        result.inherited = baseObj;
    }

    if ( typeof obj[ 'setInherited' ] === 'function' )
    {
        obj[ 'setInherited' ]( baseObj );
    }

    return result;
}

// function ensureNamespace( namespace ) {
//     //TODO
// }

module.exports = {
    inheritWithExtend: inheritWithExtend,
    inherit: inherit,
    inheritNoNs: inheritNoNs,
    // ensureNamespace: ensureNamespace
};

