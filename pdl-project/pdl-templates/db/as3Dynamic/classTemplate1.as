[header]
package [package-name]
{
[import-block]
    
    [class-attrs] dynamic class [class-name] [inheritance]
    {
        public function [class-name]()
        {
            [parent-constructor]
        }
        
        [property-list]

        [method-list]
		
		public static function create( dbRow: Object ): [class-name]
		{
			var result:[class-name] = new [class-name]();
			result.loadFromObj( dbRow );
			return result;
		}
		
    }
}