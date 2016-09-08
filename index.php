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


   var $query;

   var $loaded_query_vars;

   var $render_post_function;

   var $post_types;
   var $metadata_key;

   function __construct() {

      $this->today = array();
      $this->today['date'] = new DateTime();
      $this->formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
      $this->today['day']     = $this->today['date']->format('d');
      $this->today['month']   = $this->today['date']->format('m');
      $this->today['year']    = $this->today['date']->format('Y');


      $this->post_types = array();

      add_action("wp_enqueue_scripts", array( $this, "load_assets") );
      date_default_timezone_set('America/Mexico_City');

      function add_query_vars_filter( $vars ){
         $vars[] = "dd";
         $vars[] = "mm";
         $vars[] = "yy";
         return $vars;
      }
      add_filter( 'query_vars', 'add_query_vars_filter' );



      $this->metadata_key = 0;



      $this->loaded_query_vars = false;



      $this->add_render_post_function( array($this, "default_render_post_function" ) );

   }


   public static function load_assets() {

      wp_enqueue_style( "calendar", plugin_dir_url( __FILE__ ) . "/assets/stylesheets/calendar.css" );

      wp_enqueue_script( "jquery", plugin_dir_url( __FILE__ ) . "/bower_components/jquery/dist/jquery.min.js" );
      wp_enqueue_script( "jquery-ui-core", plugin_dir_url( __FILE__ ) . "/bower_components/jqueryui-datepicker/core.js", array('jquery') );
      wp_enqueue_script( "jquery-ui-datepicker", plugin_dir_url( __FILE__ ) . "/bower_components/jqueryui-datepicker/datepicker.js", array('jquery') );

      wp_enqueue_script( "kernkalender", plugin_dir_url( __FILE__ ) . "/assets/js/kernkalender.js", array('jquery-ui-datepicker') );

   }


   public function add_post_type( $post_type ) {

      array_push( $this -> post_types, $post_type );

   }


   public function add_metadata_key( $key ) {

      $this->metadata_key = $key;

   }


   public function set_view( $view ) {

      $this -> view = $view;

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
               echo $this->formatter->format( $this->today['date'] );
               ?>
            </small>



            <nav>
               <div class="arrow arrow-previous eight text-left">

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
               <div class="arrow arrow-next eight text-right">

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
               $q = $this->get_date_posts_query( $date_query, $this->post_types );

               $full = $q->post_count > 0;

               // $full = 1;//$q->post_count > 0;

               if ( $full ) {

                  $params = array();

                  $params['dd'] = $i;
                  $params['mm'] = $m;
                  $params['yy'] = $y;

                  $current_uri = add_query_arg( $params );

                  $link = $current_uri;

                  $post_ids = wp_list_pluck( $q->posts, 'ID' );
               }



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

         public function get_date_posts_query( $date_query, $post_types=0, $num=-1 ) {
            if( ! $post_types ) {
               $post_types = $this->post_types;
            }

            if( $this->metadata_key ) {
               $date_vars = $date_query[0];

               $date = new DateTime( $date_vars['year'].'-'.$date_vars['month'].'-'.$date_vars['day'] );

               $args = array(
                  'posts_per_page' => $num,
                  'post_type' => $post_types,
                  'meta_query' => array(
                     array(
                        'key' => $this->metadata_key,
                        'value' => $date->format('Ymd'),
                        'compare' => '==',
                        'type' => 'DATE'
                     )
                  )
               );

            } else {

               $args = array(
                  'posts_per_page' => $num,
                  'post_type' => $post_types,
                  'date_query' => $date_query,
               );

            }

            $query = new WP_Query( $args );

            return $query;

         }


         public function load_query_vars() {

            if( ! $this->loaded_query_vars ) {

               $args = NULL;

               $d = get_query_var('dd');
               $m = get_query_var('mm');
               $y = get_query_var('yy');

               $this->view = "month";

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

               $this->view = "month";

            } elseif( strcmp($d,"") && strcmp($m,"") && strcmp($y,"")  ) {

               $date = date_parse_from_format('j-m-Y', $d . "-" . $m . "-" . $y );

               if( $date ) {

                  $this->day     = $date['day'];
                  $this->month   = $date['month'];
                  $this->year    = $date['year'];
                  $this->date    = $date;
                  $this->view = "day";

               }

            } else {

               $this->date    = $this->today['date'];

               $this->day     = $this->today['date']->format('j');
               $this->month   = $this->today['date']->format('n');
               $this->year    = $this->today['date']->format('Y');

               $this->view = "month";
            }


         }
      }


      public function render_kalender() {

         $this -> load_query_vars();

         $args = array(
            'view' => $this->view,
            'day' => $this->day,
            'month' => $this->month,
            'year' => $this->year
         );
         ob_start();

         $this -> load_date( $args );

         $html = ob_get_contents();
         ob_clean();
         return $html;
      }




      public function render_kalender_posts() {

         $this -> load_query_vars();

         ?>

         <section class="posts <?php echo implode(" ", $this->post_types ); ?>">
            <?php

            if( $this->view == "month" ) {

               $date_query = array(
                  $date_query = array(
                     'after'  => array(
                        'year'  => $this->year,
                        'month' => $this->month,
                        'day'   => 1,
                     ),
                     'before' => array(
                        'year'  => $this->year,
                        'month' => $this->month,
                        'day'   => cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year ),
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


            // $q = $this->get_date_posts_query( $date_query, array('post') );



            $args = array(
               'posts_per_page' => -1,
               'post_type' => $this->post_types,
            );


            if( $this->metadata_key ) {
               if( $this->view == "month") {

                  $date_begin = new DateTime($this->year.'-'.$this->month.'-1');
                  $date_begin = $date_begin->format('Ymd');
                  $date_end = new DateTime($this->year.'-'.$this->month.'-'.cal_days_in_month(CAL_GREGORIAN,$this->month,$this->year));
                  $date_end = $date_end->format('Ymd');

                  $meta_query_value = array( $date_begin, $date_end );
                  $meta_query_compare = 'BETWEEN';

               }
               if( $this->view == "day") {

                  $date = new DateTime($this->year.'-'.$this->month.'-'.$this->day );
                  $date = $date->format('Ymd');

                  $meta_query_value = $date;
                  $meta_query_compare = '==';

               }
               $args['meta_query'] = array(
                  array(
                     'key' => $this->metadata_key,
                     'value' => $meta_query_value,
                     'compare' => $meta_query_compare,
                     'type' => 'DATE'
                  )
               );

            } else {
               $args['date_query'] = $date_query;
            }


            $q = new WP_Query( $args );

            ob_start();

            if($q->have_posts() ) {
               while ( $q->have_posts() ) {
                  $q->the_post();

                  $this->render_post();

               }
            }

            $html = ob_get_contents();
            ob_clean();
            return $html;

            ?>

         </section>

         <?php

      }



      public function render_post() {
         call_user_func( $this->render_post_function, $this );
      }


      public function add_render_post_function( $function ) {
         $this->render_post_function = $function;
      }

      public function default_render_post_function() {
         global $post;
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


      public function render_kalender_future_posts() {
         $args = array(
            'posts_per_page' => -1,
            'post_type' => 'date-cpt',
            'meta_query' => array(
               array(
                  'key' => $this->metadata_key,
                  'value' => date('Ymd'),
                  'compare' => '>=',
                  'type' => 'DATE'
               )
            )
         );

         $q = new WP_Query( $args );
         ob_start();
         if($q->have_posts() ) {
            while ( $q->have_posts() ) {
               $q->the_post();

               $this->render_post();

            }
         }

         $html = ob_get_contents();
         ob_clean();
         return $html;
      }

      public function render_kalender_past_posts() {

         $args = array(
            'posts_per_page' => -1,
            'post_type' => $this->post_types,
            'meta_query' => array(
               array(
                  'key' => $this->metadata_key,
                  'value' => date('Ymd'),
                  'compare' => '<',
                  'type' => 'DATE'
               )
            )
         );

         $q = new WP_Query( $args );

         ob_start();
         
         if($q->have_posts() ) {
            while ( $q->have_posts() ) {
               $q->the_post();

               $this->render_post();

            }
         }

         $html = ob_get_contents();
         ob_clean();
         return $html;
      }

   }





   add_action('init', 'calendar_init');
   function calendar_init() {

      setlocale(LC_TIME, "es_ES.UTF-8" );

      $calendar = new KernKalender();

      $calendar -> set_view('month');

      $calendar -> add_post_type('date-cpt');

      $calendar -> add_metadata_key('test-cpt-date');

      $newFunc = function($calendar) {

         global $post;
         $ID = get_the_ID();
         $date = get_post_meta( get_the_ID(), $calendar->metadata_key, true );
         $link = get_the_permalink( $ID );
         $title = get_the_title();
         $image = get_the_post_thumbnail();
         $excerpt = get_the_excerpt();

         ?>

         <a href="<?php echo $link; ?>">

            <article>

               <h1>
                  <?php echo $title; ?>
               </h1>

               <small class="date">
                  <?php echo $date; ?>
               </small>
               <div class="image">
                  <?php echo $image; ?>
               </div>

               <div class="excerpt">
                  <?php echo $excerpt; ?>
               </div>

            </article>

         </a>
         <?php

      };

      $calendar -> add_render_post_function( $newFunc );


      add_shortcode( 'kalender', array( $calendar,'render_kalender'));

      add_shortcode( 'kalender_posts', array( $calendar,'render_kalender_posts'));

      add_shortcode( 'future_posts', array( $calendar, 'render_kalender_future_posts'));

      add_shortcode( 'past_posts', array( $calendar, 'render_kalender_past_posts'));


      add_filter('widget_text','do_shortcode');

   }


   ?>
