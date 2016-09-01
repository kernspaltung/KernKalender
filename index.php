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
   public static function do_calendar() {

      $view = "month";


      $date = strtotime( "today" ); //$month . "/" . $day . "/" . $year );
      $monthName = strftime( "%B", $date );
      $dayName     = strftime( "%A", $date );
      $day     = strftime( "%e", $date );
      $month   = strftime( "%m", $date );
      $year    = strftime( "%G", $date );

      ?>
      <nav id="calendar " class="calendar w_100 h_100">

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
                  ?>
                  <div class="weekday empty button enabled <?php echo $i==$day ? ' today ' : ''; ?>" style="">
                     <?php echo $i; ?>
                  </div>
                  <?php
               }


               ?>
            </ul>
         </section>

      </nav>
      <?php
   }

}

$calendar = new Calendar();
add_shortcode( 'calendar', array( $calendar,'do_calendar'));

?>
