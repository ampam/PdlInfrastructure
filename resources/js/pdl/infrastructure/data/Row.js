

/**
 *
 * @class {Row}
 * @property {object} __propertyAttributes
 */
function Row() {

    /**
     * @returns int
     */
    this.getDbId = () => {

        const propertyName = this.getPropertyByAttribute( 'IsDbId' );

        const result = this[ propertyName ];
        return result;
    };

    /**
     *
     * @param {string} attributeName
     *
     * @returns {string}
     */
    this.getPropertyByAttribute = ( attributeName ) => {

        let result = '';

        for ( const propertyName in this.__propertyAttributes || {} )
        {
            if ( this.__propertyAttributes.hasOwnProperty( propertyName ) )
            {
                const attributes = this.__propertyAttributes[ propertyName ] || [];
                for ( let i = 0; i < attributes.length && result === ''; i++ )
                {
                    if ( attributes[ i ].name === attributeName )
                    {
                        result = propertyName;
                    }
                }
            }

            if ( result !== '' )
            {
                break;
            }
        }

        return result;
    };

}
global.com = global.com || {};
com.mh = com.mh || {};
com.mh.ds = com.mh.ds || {};
com.mh.ds.infrastructure = com.mh.ds.infrastructure || {};
com.mh.ds.infrastructure.data = com.mh.ds.infrastructure.data || {}
com.mh.ds.infrastructure.data.Row = Row;
module.exports = Row;
