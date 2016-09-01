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
   public function render_html( $args ) {

      if( is_array($args) ) {
         if( array_key_exists('view', $args) ) {
            $view = $args['view'];
         }
         if( array_key_exists('day', $args) ) {
            $day = $args['day'];
         }
         if( array_key_exists('month', $args) ) {
            $month = $args['month'];
         }
         if( array_key_exists('year', $args) ) {
            $year = $args['year'];
         }
      } else {
         die;
      }

      $args_date = strtotime( $month . "/" .  $day . "/" .  $year );
      // echo strftime( "%e", $args_date );

      $week_day_initials = ["l", "m", "m", "j", "v", "s", "d" ];

      $today_date = strtotime( "today" ); //$month . "/" . $day . "/" . $year );
      $today_monthName = strftime( "%B", $today_date );
      $today_dayName     = strftime( "%A", $today_date );
      $today_day     = strftime( "%e", $today_date );
      $today_month   = strftime( "%m", $today_date );
      $today_year    = strftime( "%G", $today_date );

      $date = strtotime( "today" ); //$month . "/" . $day . "/" . $args_year );
      $monthName = strftime( "%B", $args_date );
      $dayName     = strftime( "%A", $args_date );
      $day     = strftime( "%e", $args_date );
      $month   = strftime( "%m", $args_date );
      $year    = strftime( "%G", $args_date );
      ?>
      <section id="calendar " class="calendar w_100 h_100">

         <header>

<?php if( $view == "month" ) {

   ?>
   <small>
      <?php echo $today_dayName . ", " . $today_day . " de " . $today_monthName . ", " . $today_year; ?>
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
                     <sup class="day-number">
                        <?php echo $i; ?>
                     </sup>
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

   public function start_calendar() {

         $args = array(
            'view'=>'month',
            'day'=>'20',
            'month'=>'8',
            'year'=>'2016',
         );


         $this->render_html($args);


   }

}



add_action('init', 'calendar_init');

function calendar_init() {
   $calendar = new Calendar();


   add_shortcode( 'calendar', array( $calendar,'start_calendar'));
}

?>
