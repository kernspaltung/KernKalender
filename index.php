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
   $formatter,
   $view;

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
       $vars[] = "dd";
       $vars[] = "mm";
       $vars[] = "yy";
       return $vars;
      }
      add_filter( 'query_vars', 'add_query_vars_filter' );

   }


   public static function load_assets() {
      wp_enqueue_style("calendar", plugin_dir_url( __FILE__ ) . "/assets/stylesheets/calendar.css" );
   }


   public function arrow_link($direction="previous", $view,$month,$year,$day ) {

      $params = array();


      $increment = $direction == "previous" ? -1 : 1;
      if( $view == "month" ) {

      $arrow_month=(( $month + $increment )%12);

      $arrow_year=$year;
      if( $arrow_month == 0 || $arrow_month == 13 )
         $arrow_year = $year+$increment;


      }
      if( $view == "day" ) {

         $arrow_month=$month;
         $arrow_year=$year;

         $arrow_month_days = cal_days_in_month(CAL_GREGORIAN, $arrow_month, $arrow_year);

         $arrow_day=$day + $increment;

         if( $arrow_day <= 0 || $arrow_day > $arrow_month_days ) {

            $arrow_month += $increment;

            if( $arrow_month <= 0 || $arrow_month >= 13 ){

               if( $arrow_month <= 0 ){
                  $arrow_month = 12;
               }
               if( $arrow_month > 12 ) {
                  $arrow_month = 1;
               }
               $arrow_year += $increment;

            }

            if( $arrow_day > $arrow_month_days ) {
               $arrow_day = 1;
            }

            $arrow_month_days = cal_days_in_month(CAL_GREGORIAN, $arrow_month, $arrow_year);

            if( $arrow_day <= 0 ){
               $arrow_day = $arrow_month_days;
            }


         }

         $params['dd'] = $arrow_day;

      }

      $params['mm'] = $arrow_month;
      $params['yy'] = $arrow_year;


      $current_uri = add_query_arg( $params );
      $link = $current_uri;
      return $link;

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
               echo "<b>hoy:</b> " . $this->formatter->format( $this->today['date'] );
               ?>
            </small>



            <nav>
               <div class="arrow-previous eight text-left">

                  <a href="<?php echo $this->arrow_link("previous",$view,$month,$year,$day); ?>">
                     previous
                  </a>
               </div>
               <div class="three-quarters text-center">
                  <h2>
                     <?php
                     switch( $view ) {
                        case "month":
                           $this->formatter->setPattern("MMMM");
                           echo $this->formatter->format( $this->date );
                           break;
                        case "day":
                           echo $day . " de ";
                           echo strftime("%B",strtotime($month.'/'.$day.'/'.$year)) . ", ";
                           echo $year;
                           break;
                     }

                     ?>
                  </h2>
               </div>
               <div class="arrow-next eight text-right">

                  <a href="<?php echo $this->arrow_link("next",$view,$month,$year,$day); ?>">
                     next
                  </a>
               </div>
            </nav>


         </header>
         <?php $html_id = "calendar-". $view ."-view"; ?>
         <section id="<?php echo $html_id; ?>" class="calendar-view <?php echo $html_id; ?>">

         <?php
         // switch( $view ) {
         //    case "day":
         //       $this->render_day_view($day,$month,$year);
         //       break;
         //    case "month":
         //       $this->render_month_view($day,$month,$year);
         //       break;
         // }
         $this->render_month_view($day,$month,$year);

            ?>

         </section>

      <?php
   }

   public function render_month_view($d,$m,$y) {

      $week_day_initials = ["l", "m", "m", "j", "v", "s", "d" ];

      ?>


         <header>


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
               <div class="day invisible" style="">
               </div>
               <?php
            }


            for ($i=1; $i <= $days_in_month; $i++) {
               // $date = strtotime( $m . "/" . $d . "/" . $y );
               // $date = strtolower($date);
               $date_query = array(
                  array(
                     'day'  => $i,
                     'month' => $m,
                     'year'   => $y,
                  ),
               );
               $q = $this->get_date_posts_query( $date_query, array('post') );

               $full = $q->post_count > 0;

               // $full = 1;//$q->post_count > 0;

               if ( $full ) {

                  $params = array();

                  $params['dd'] = $i;
                  $params['mm'] = $m;
                  $params['yy'] = $y;

                  $current_uri = add_query_arg( $params );

                  $link = $current_uri;

               }

               $post_ids = wp_list_pluck( $q->posts, 'ID' );


               ?>
               <div class="day button enabled <?php #  echo $i==$d ? ' today ' : ''; ?> <?php echo $full ? 'full' : 'empty'; ?>" data-posts="<?php echo json_encode($post_ids); ?>">
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



   }



   public function get_date_posts_query( $date_query, $post_types=array('post'), $num=-1 ) {

      $args = array(
         'posts_per_page' => $num,
         'post_type' => $post_types,
         'date_query' => $date_query,
      );

      $query = new WP_Query( $args );

      return $query;

   }




   public function render_kalender() {

      $args = NULL;

      $d = get_query_var('dd');
      $m = get_query_var('mm');
      $y = get_query_var('yy');

      $view = "month";

      if( $d == "" && $m != "" && $y != "" ) {

         $this->day     = 1;
         $this->month   = $m;
         $this->year    = $y;

         $this->date    = date_create_from_format(
            'j-n-Y',
            $this->day . "-" .
            $this->month . "-" .
            $this->year
         );

         $view = "month";

      } elseif( strcmp($d,"") && strcmp($m,"") && strcmp($y,"")  ) {


         $date = date_parse_from_format('j-m-Y', $d . "-" . $m . "-" . $y );

         if( $date ) {

            $this->day     = $date['day'];
            $this->month   = $date['month'];
            $this->year    = $date['year'];
            $this->date    = $date;
            $view = "day";

         }


      } else {

         $this->date    = $this->today['date'];

         $this->day     = $this->today['date']->format('j');
         $this->month   = $this->today['date']->format('n');
         $this->year    = $this->today['date']->format('y');

         $view = "month";
      }

      $this->view = $view;

      $args = array(
         'view' => $view,
         'day' => $this->day,
         'month' => $this->month,
         'year' => $this->year
      );

      $this -> load_date( $args );

   }




   public function render_kalender_posts() {

      ?>

         <section class="posts">
            <?php

               if( $this->view == "month" ) {

                  $date_query = array(
                     $date_query = array(
                        'after'  => array(
                  			'year'  => 2016,
                  			'month' => 8,
                  			'day'   => 1,
                  		),
                  		'before' => array(
                  			'year'  => 2016,
                  			'month' => 8,
                  			'day'   => 31,
                  		),
                  		'inclusive' => true,
                     )
                  );

               } else {
                  $date_query = array(
                     array(
                        'day'  => $this->day,
                        'month' => $this->month,
                        'year'   => $this->year,
                     ),
                  );

               }


               $q = $this->get_date_posts_query( $date_query, array('post') );

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

}





add_action('init', 'calendar_init');
function calendar_init() {

   setlocale(LC_TIME, "es_ES.UTF-8" );
   $calendar = new KernKalender();


   add_shortcode( 'kalender', array( $calendar,'render_kalender'));

   add_shortcode( 'kalender_posts', array( $calendar,'render_kalender_posts'));


   add_filter('widget_text','do_shortcode');

}


?>
