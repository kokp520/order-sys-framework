<?php
/**
*
*
* Template Name: Blank Canvas 
*
*
*
*/

get_header();


?>
<div class="blank-canvas asthir-canvas">

				<?php
				while ( have_posts() ) :
					the_post();

					the_content();

				endwhile; // End of the loop.
				?>
</div> <!-- end canvas -->

<?php
get_footer();