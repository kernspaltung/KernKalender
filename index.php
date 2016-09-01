<?php
/*
Plugin Name: KernKalender
Author: kernspaltung!
Description: Flexible KernKalender for WordPress developers. Features API and with automatic view rendering and shortcodes for dummies
*/

class KernKalender {

   var
   $today,
   $date,
   $day,
   $month,
   $year,
   $formatter;

   function __construct() {

      $this->today = array();
      $this->today['date'] = new DateTime();
      $this->formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
      $this->today['day']     = $this->today['date']->format('d');
      $this->today['month']   = $this->today['date']->format('m');
      $this->today['year']    = $this->today['date']->format('Y');




      add_action("wp_enqueue_scripts", array( $this, "load_assets") );
      date_default_timezone_set('America/Mexico_City');

      function add_query_vars_filter( $vars ){
       $vars[] = "d";
      //  $vars[] = "m";
      //  $vars[] = "y";
       return $vars;
      }
      add_filter( 'query_vars', 'add_query_vars_filter' );

   }


   public static function load_assets() {
      wp_enqueue_style("calendar", plugin_dir_url( __FILE__ ) . "/assets/stylesheets/calendar.css" );
   }


   public function load_date( $args ) {

      if( $args) {
         if( is_array( $args ) ) {

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


         }

      } else {

         $view = "month";
         $day = $this->today['day'];
         $month = $this->today['month'];
         $year = $this->today['year'];

      }



      ?>


      <section id="calendar " class="calendar w_100 h_100">

         <header>
            <small>
               <?php
               $this->formatter->setPattern("EEEE d 'de' MMMM', 'yyyy");
               echo $this->formatter->format( $this->date );
               ?>
            </small>
         </header>
         <?php $html_id = "calendar-". $view ."-view"; ?>
         <section id="<?php echo $html_id; ?>" class="calendar-view <?php echo $html_id; ?>">

         <?php
         switch( $view ) {
            case "day":
               $this->render_day_view($day,$month,$year);
               break;
            case "month":
               $this->render_month_view($day,$month,$year);
               break;
         }

            ?>

         </section>

      <?php
   }

   public function render_month_view($d,$m,$y) {

      $week_day_initials = ["l", "m", "m", "j", "v", "s", "d" ];

      ?>


         <header>

            <nav>
               <div class="arrow-previous eight text-left">
                  previous
               </div>
               <div class="three-quarters text-center">
                  <h2>
                     <?php
                     $this->formatter->setPattern("MMMM");
                     echo $this->formatter->format( $this->date );
                     ?>
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


            $days_in_month = cal_days_in_month( CAL_GREGORIAN, $m, $y );

            $date_day1 = strtotime( $m.'/1/'.$y );

            $num_day1 = strftime("%u", $date_day1 );

            for ($i=1; $i <= $num_day1 - 1; $i++) {
               ?>
               <div class="weekday empty button disabled" style="">
               </div>
               <?php
            }


            for ($i=1; $i <= $days_in_month; $i++) {
               $date = strtotime( $m . "/" . $d . "/" . $y );
               $name_week_day = strftime("%A", $date );
               // $date = strtolower($date);

               $q = $this->get_date_posts_query( $i, $m, $y );

               $full = $q->post_count > 0;

               if ( $full ) {
                  $this->formatter->setPattern("dd'-'MM'-'yyyy");

                  $current_uri = add_query_arg( 'd', $this->formatter->format( $date ) );

                  $link = $current_uri;

               }

               $post_ids = wp_list_pluck( $q->posts, 'ID' );


               ?>
               <div class="day button enabled <?php echo $i==$d ? ' today ' : ''; ?> <?php echo $full ? 'full' : 'empty'; ?>" data-posts="<?php echo json_encode($post_ids); ?>">
                  <?php echo $full ? '<a href="'.$link.'">' : ''; ?>
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
                  <?php echo $full ? '</a>' : ''; ?>
               </div>
               <?php
            }


            ?>
         </ul>

      <?php

   }

   public function render_day_view($d,$m,$y) {

      ?>

         <header>

            <nav>
               <div class="arrow-previous eight text-left">
                  previous
               </div>
               <div class="three-quarters text-center">
                  <h2>
                     <?php
                     echo $d . " de ";

                     echo strftime("%B",strtotime($m.'/'.$d.'/'.$y)) . ", ";

                     echo $y;

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

               $q = $this->get_date_posts_query( $d, $m, $y );

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

      $args = NULL;
      $d = get_query_var('d');
      if( $d != "" ) {

         $date = date_parse_from_format('j-m-Y', $d);


         if( $date ) {

            $this->day     = $date['day'];
            $this->month   = $date['month'];
            $this->year    = $date['year'];
            $this->date    = $date;

            $view = "day";


            $args = array(
               'view' => $view,
               'day' => $this->day,
               'month' => $this->month,
               'year' => $this->year,
            );
         }
      } else {

         $this->day     = strftime( "%e", $this->today['day'] );
         $this->month   = strftime( "%m", $this->today['month'] );
         $this->year    = strftime( "%G", $this->today['year'] );
         $this->date    = $this->today['date'];

         $view = "month";

         $args = array(
            'view' => "month",
            'day' => 26,
            'month' => 8,
            'year' => 2016
         );
      }





         $this -> load_date( $args );


   }

}



add_action('init', 'calendar_init');

function calendar_init() {

   setlocale(LC_TIME, "es_ES.UTF-8" );
   $calendar = new KernKalender();


   add_shortcode( 'calendar', array( $calendar,'start_calendar'));



}


?>
