<?php
/*
Plugin Name: KernKalender
Author: kernspaltung!
Description: Flexible Calendar for WordPress developers. Features API and with automatic view rendering and shortcodes for dummies
*/

class Calendar {

   var
   $today_date,
   $today_monthName,
   $today_dayName,
   $today_day,
   $today_month,
   $today_year,

   $date,
   $monthName,
   $dayName,
   $day,
   $month,
   $year;

   function __construct() {
      add_action("wp_enqueue_scripts", array( $this, "load_assets") );
      date_default_timezone_set('America/Mexico_City');

   }


   public static function load_assets() {
      wp_enqueue_style("calendar", plugin_dir_url( __FILE__ ) . "/assets/stylesheets/calendar.css" );
   }
   public function load_view( $args ) {

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


      $today_date = strtotime( "today" ); //$month . "/" . $day . "/" . $year );
      $this->today_monthName = strftime( "%B", $today_date );
      $this->today_dayName     = strftime( "%A", $today_date );
      $this->today_day     = strftime( "%e", $today_date );
      $this->today_month   = strftime( "%m", $today_date );
      $this->today_year    = strftime( "%G", $today_date );

      $this->date = strtotime( "today" ); //$month . "/" . $day . "/" . $args_year );
      $this->monthName = strftime( "%B", $args_date );
      $this->dayName     = strftime( "%A", $args_date );
      $this->day     = strftime( "%e", $args_date );
      $this->month   = strftime( "%m", $args_date );
      $this->year    = strftime( "%G", $args_date );

      ?>


      <section id="calendar " class="calendar w_100 h_100">

         <header>
            <small>
               <?php echo $this->today_dayName . ", " . $this->today_day . " de " . $this->today_monthName . ", " . $this->today_year; ?>
            </small>
         </header>
         <?php $html_id = "calendar-". $view ."-view"; ?>
         <section id="<?php echo $html_id; ?>" class="calendar-view <?php echo $html_id; ?>">

         <?php
         switch( $view ) {
            case "day":
               $this->render_day_view();
               break;
            case "month":
               $this->render_month_view();
               break;
         }

            ?>

         </section>

      <?php
   }

   public function render_month_view() {

      $week_day_initials = ["l", "m", "m", "j", "v", "s", "d" ];

      ?>


         <header>

            <nav>
               <div class="arrow-previous eight text-left">
                  previous
               </div>
               <div class="three-quarters text-center">
                  <h2>
                     <?php echo $this->monthName; ?>
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


            $days_in_month = cal_days_in_month( CAL_GREGORIAN, $this->month, $this->year );

            $date = strtotime( $this->month.'/1/'.$this->year);

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

               $q = $this->get_date_posts_query( $i, $this->month, $this->year );

               $post_ids = wp_list_pluck( $q->posts, 'ID' )

               ?>
               <div class="day button enabled <?php echo $i==$this->day ? ' today ' : ''; ?> <?php echo $q->post_count > 0 ? 'full' : 'empty'; ?>" data-posts="<?php echo json_encode($post_ids); ?>">
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

      <?php

   }

   public function render_day_view() {


      ?>

         <header>

            <nav>
               <div class="arrow-previous eight text-left">
                  previous
               </div>
               <div class="three-quarters text-center">
                  <h2>
                     <?php
                     echo $this->day . ", ";

                     echo $this->dayName . ", ";

                     echo $this->monthName . ", ";

                     ?>
                  </h2>
               </div>
               <div class="arrow-next eight text-right">
                  next
               </div>
            </nav>


         </header>
         <section class="posts">
            <?php

               $q = $this->get_date_posts_query( $this->day, $this->month, $this->year );

               if($q->have_posts() ) {
                  while ( $q->have_posts() ) {
                     $q->the_post();
                     $ID = get_the_ID();
                     $link = get_the_permalink( $ID );
                     $title = get_the_title();
                     $image = get_the_post_thumbnail();
                     $excerpt = get_the_excerpt();
                     ?>
                     <a href="<?php echo $link; ?>">
                        <article>
                           <h6>
                              <?php echo $title; ?>
                           </h6>
                           <div class="image">
                              <?php echo $image; ?>
                           </div>
                           <div class="excerpt">
                              <?php echo $excerpt; ?>
                           </div>
                        </article>
                     </a>
                     <?php
                  }
               }


            ?>

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
            'view'=>'day',
            'day'=>'26',
            'month'=>'8',
            'year'=>'2016',
         );
         //
         // $args = array(
         //    'view'=>'month',
         //    'day'=>'20',
         //    'month'=>'8',
         //    'year'=>'2016',
         // );


         $this->load_view($args);


   }

}



add_action('init', 'calendar_init');

function calendar_init() {
   $calendar = new Calendar();


   add_shortcode( 'calendar', array( $calendar,'start_calendar'));
}

?>
