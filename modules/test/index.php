<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/

$co = eZContentObject::fetch( 2467 );
//print_r( $co );

//print_r( $co->attribute( 'state_id_array' ) );

// $co->attribute( 'state_id_array' )

print_r( $co->allowedAssignStateIDList() );
