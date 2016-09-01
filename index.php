<?php
/*
Plugin Name: KernKalender
Author: kernspaltung!
Description: Flexible Calendar for WordPress developers. Features API and with automatic view rendering and shortcodes for dummies
*/

class Calendar {
   function __construct() {
      add_action("wp_enqueue_scripts", array( $this, "load_assets") );
      date_default_timezone_set('America/Mexico_City');

   }
   public static function load_assets() {
      wp_enqueue_style("calendar", plugin_dir_url( __FILE__ ) . "/assets/stylesheets/calendar.css" );
   }
   public function do_calendar() {

      $view = "month";

      $week_day_initials = ["l", "m", "m", "j", "v", "s", "d" ];

      $date = strtotime( "today" ); //$month . "/" . $day . "/" . $year );
      $monthName = strftime( "%B", $date );
      $dayName     = strftime( "%A", $date );
      $day     = strftime( "%e", $date );
      $month   = strftime( "%m", $date );
      $year    = strftime( "%G", $date );

      ?>
      <section id="calendar " class="calendar w_100 h_100">

         <header>

<?php if( $view == "month" ) {

   ?>
   <small>
      <?php echo $dayName . ", " . $day . " de " . $monthName . ", " . $year; ?>
   </small>

   <?php } ?>
         </header>

         <section id="calendar-view" class="calendar-view">
            <header>

               <nav>
                  <div class="arrow-previous eight text-left">
                     previous
                  </div>
                  <div class="three-quarters text-center">
                     <h2>
                        <?php echo $monthName; ?>
                     </h2>
                  </div>
                  <div class="arrow-next eight text-right">
                     next
                  </div>
               </nav>

               <nav class="week-days">
                  <?php for ($i=0; $i < 7; $i++) {
                     ?>
                     <div class="seventh text-center">
                        <?php echo $week_day_initials[$i]; ?>
                     </div>
                     <?php
                  } ?>
               </nav>

            </header>
            <ul>
               <?php
               // function get_weekdays($m,$y) {


               $days_in_month = cal_days_in_month( CAL_GREGORIAN, $month, $year );

               $date = strtotime( $month.'/1/'.$year);

               $num_week_day1 = $date = strftime("%u", $date );

               for ($i=1; $i <= $num_week_day1 - 1; $i++) {
                  ?>
                  <div class="weekday empty button disabled" style="">
                  </div>
                  <?php
               }


               for ($i=1; $i <= $days_in_month; $i++) {
                  $date = strtotime( $date );
                  $name_week_day = $date = strftime("%A", $date );
                  $date = strtolower($date);

                  $q = $this->get_date_posts_query( $i, $month, $year );

                  $post_ids = wp_list_pluck( $q->posts, 'ID' )

                  ?>
                  <div class="day button enabled <?php echo $i==$day ? ' today ' : ''; ?> <?php echo $q->post_count > 0 ? 'full' : 'empty'; ?>" data-posts="<?php echo json_encode($post_ids); ?>">
                     <div class="day-number">
                        <?php echo $i; ?>
                     </div>
                     <div class="day-posts">
                        <?php
                        if( $q->post_count > 0 ) {

                        ?>
                        (
                           <span class="post-count">
                              <?php echo $q->post_count; ?>
                           </span>
                        )
                        <?php
                        }
                        ?>
                     </div>
                  </div>
                  <?php
               }


               ?>
            </ul>
         </section>

      </section>
      <?php
   }

   public function get_date_posts_query( $d, $m, $y ) {

      $args = array(
         'date_query' => array(
      		array(
      			'year'  => $y,
      			'month' => $m,
      			'day'   => $d,
      		),
      	),
      );
      $query = new WP_Query( $args );

      return $query;
   }

}

$calendar = new Calendar();
add_shortcode( 'calendar', array( $calendar,'do_calendar'));

?>
