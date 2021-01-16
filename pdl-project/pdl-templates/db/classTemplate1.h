[header]
#include "properties.h"
#include "attributes.h"
//Note: only valid in C++11

//using namespace pam::properties::attributes
[using-block]

[namespace-block]
//namespace pam { namespace properties { namespace attributes
//{

    [class-attrs] class [class-name] [inheritance]
    {
        [property-list]
        /*
        //property example
        PAM_DECLARE_PROPERTY( int, _refreshInterval, RefreshInterval );
        */
        
        /*
        propertyAttributes _propertyAttributes;
        
        private void initPropertyAttributes()
        {
            auto refreshIntervalAttrs = attributeInfoListPtr( new attributeInfoList() );
            
            attributeInfo refreshInterval_spinnerAttr;
            refreshInterval_spinnerAttr.name = "spinner";
            refreshInterval_spinnerAttr.addValue( "min", 1 );
            refreshInterval_spinnerAttr.addValue( "max", 100 );
            refreshInterval_spinnerAttr.addValue( "description", "asdsadsad" );
            refreshInterval_spinnerAttr.addValue( "visible", true );
            refreshInterval_spinnerAttr.addValue( "percentage", 10.5 );
            refreshIntervalAttrs.push_back( refreshInterval_spinnerAttr );
            
            attributeInfo refreshInterval_descriptionAttr;
            refreshInterval_descriptionAttr.name = "description";
            refreshInterval_descriptionAttr.addValue( "default1", "blah blah blah" );
            refreshIntervalAttrs.push_back( refreshInterval_descriptionAttr );            
            
            _propertyAttributes[ "RefreshInterval" ] = refreshIntervalAttrs;
            
        }
        */
        
        [method-list]

    }


//}}};