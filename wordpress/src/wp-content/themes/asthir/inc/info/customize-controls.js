( function( api ) {

	// Extends our custom "asthir-pro" section.
	api.sectionConstructor['asthir-pro'] = api.Section.extend( {

		// No events for this type of section.
		attachEvents: function () {},

		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );

} )( wp.customize );
