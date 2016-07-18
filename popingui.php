#!/usr/bin/php
<?php

/**
 * UNFINISHED AND DEPRECATED
 * placed here only for historical value
 * i don't know working it now or not
 *
 * requiring ncurses module
 * for install on ubuntu 12.04 try
 * apt-get -f install libncursesw5-dev php5-dev php-pear
 * pecl install ncurses
 * echo "extension=ncurses.so" >> /etc/php5/cli/php.ini
 */

error_reporting( -1 );

ncurses_init(); // начинаем с инициализации библиотеки
ncurses_newwin( 0, 0, 0, 0 ); // используем весь экран
ncurses_border( 0, 0, 0, 0, 0, 0, 0, 0 ); // рисуем рамку вокруг окна
ncurses_refresh(0); // рисуем окна

/*************settings*/
$cell_height = 3; //
$cell_width  = 20;
$cell_x      = 0;
$cell_y      = 0;

$interval = 1; //interval between cycle iterations
/**************/

/*************TABLE COLUMNS*/
$header[] = "Description";
$header[] = "IP (specified)";
$header[] = "IP (determined)";
$header[] = "PING (current)";
$header[] = "PING (average)";
$header[] = "PING (minimal)";
$header[] = "PING (maximal)";
$header[] = "TTL";
$header[] = "PACKETS (sent)";
$header[] = "PACKETS (lost)";
$header[] = "PACKETS (lost %)";
/**************/

/*************TARGETS*/
/*$targets[] = array(
	'address' => '192.168.1.1',
    'target_name' => 'router',
	'packetsize' => '65500'
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);
$targets[] = array(
	'address' => '000',
	'target_name' => '000',
	'packetsize' => '000',
);*/


$targets[1]['description'] = "WCall step1";
$targets[1]['ip']          = "84.52.99.33 -s 1472";
$targets[2]['description'] = "WCall step2";
$targets[2]['ip']          = "109.167.214.97 -s 1472";
$targets[3]['description'] = "WCall step3";
$targets[3]['ip']          = "109.167.214.98 -s 1472";
$targets[4]['description'] = "WCall step4";
$targets[4]['ip']          = "109.167.195.140 -s 1472";
$targets[5]['description'] = "WCall step5";
$targets[5]['ip']          = "109.167.214.90 -s 1472";

$targets[6]['description'] = "google spb ix";
$targets[6]['ip']          = "194.226.100.138 -s 1472";
$targets[7]['description'] = "google dns";
$targets[7]['ip']          = "8.8.8.8";

$targets[8]['description']  = "yandex";
$targets[8]['ip']           = "ya.ru -s 1472";
$targets[9]['description']  = "mailru";
$targets[9]['ip']           = "mail.ru -s 1472";
$targets[10]['description'] = "google";
$targets[10]['ip']          = "google.com";

$targets[11]['description'] = "infoboxcloud";
$targets[11]['ip']          = "infoboxcloud.ru -s 1472";
$targets[12]['description'] = "sprinthost";
$targets[12]['ip']          = "sprinthost.ru -s 1472";
$targets[13]['description'] = "timeweb";
$targets[13]['ip']          = "timeweb.ru";
$targets[14]['description'] = "sweb";
$targets[14]['ip']          = "sweb.ru -s 1472";

$targets[15]['description'] = "home server";
$targets[15]['ip']          = "reiltech.tk -s 1472";
/**************/

/*************CREATING AND DRAWING DATAGRID*/
for ( $i = 0; $i < count( $targets ) + 1; $i ++ ) {
	for ( $j = 0; $j < count( $header ); $j ++ ) {
		$grid[ $i ][ $j ] = ncurses_newwin( 3, 20, 0 + 2 * $i, 0 + 19 * $j );
		ncurses_wborder( $grid[ $i ][ $j ], 0, 0, 0, 0, 0, 0, 0, 0 );
		//ncurses_mvwaddstr($grid[$i][$j], 1, 1, $i."_".$j);
		//ncurses_wrefresh($grid[$i][$j]);
	}
}
/**************/

/*************FILL UP HEADER*/
$grid = array();
for ( $j = 0; $j < count( $header ); $j ++ ) {
	ncurses_mvwaddstr( $grid[0][ $j ], 1, 1, $header[ $j ] );
	ncurses_wrefresh( $grid[0][ $j ] );
}
/**************/

/*************FILL UP PREDEFINED CELLS*/
for ( $j = 1; $j < count( $targets ) + 1; $j ++ ) {
	ncurses_mvwaddstr( $grid[ $j ][0], 1, 1, $targets[ $j - 1 ][ 'description' ] );
	ncurses_wrefresh( $grid[ $j ][0] );
	if ( strpos( $targets[ $j - 1 ][ 'ip' ], ' ' ) ) {
		$temp = substr( $targets[ $j - 1 ][ 'ip' ], 0, strpos( $targets[ $j - 1 ][ 'ip' ], ' ' ) );
	} else {
		$temp = $targets[ $j - 1 ][ 'ip' ];
	}
	ncurses_mvwaddstr( $grid[ $j ][1], 1, 1, $temp );
	ncurses_wrefresh( $grid[ $j ][1] );
}
/**************/

/*************INFINITE CYCLE OF PING*/
$t0 = 0;
foreach ( $targets as &$ip ) {
	$ip[ 'min' ] = 4444;
	$ip[ 'max' ] = 0;
}
while ( $t0 == 0 ) {
	for ( $j = 0; $j < count( $targets ); $j ++ ) {
		$targets[ $j ][ 'current' ] = exec( 'ping -c 1 -W 2 ' . $targets[ $j ][ 'ip' ] );

		if ( ! ( strpos( $targets[ $j ][ 'current' ], "unknown host" ) ) or ! ( strpos( $targets[ $j ][ 'current' ], "100% packet loss" ) ) ) {

			$targets[ $j ][ 'current' ] = substr( $targets[ $j ][ 'current' ], strpos( $targets[ $j ][ 'current' ], "= " ) + 2, strlen( $targets[ $j ]['current'] ) - strpos( $targets[ $j ]['current'], "= " ) - 2 );
			$targets[ $j ][ 'current' ] = substr( $targets[ $j ][ 'current' ], 0, strpos( $targets[ $j ][ 'current' ], "/" ) );

			if ( $targets[ $j ][ 'current' ] < $targets[ $j ][ 'min' ] ) {

				$targets[ $j ][ 'min' ] = $targets[ $j ][ 'current' ];

				ncurses_wclear( $grid[ $j + 1 ][5] );
				ncurses_wborder( $grid[ $j + 1 ][5], 0, 0, 0, 0, 0, 0, 0, 0 );
				ncurses_mvwaddstr( $grid[ $j + 1 ][5], 1, 1, $targets[ $j ][ 'min' ] );
				ncurses_wrefresh( $grid[ $j + 1 ][5] );
			}

			if ( $targets[ $j ][ 'current' ] > $targets[ $j ][ 'max' ] ) {

				$targets[ $j ][ 'max' ] = $targets[ $j ][ 'current' ];

				ncurses_wclear( $grid[ $j + 1 ][6] );
				ncurses_wborder( $grid[ $j + 1 ][6], 0, 0, 0, 0, 0, 0, 0, 0 );
				ncurses_mvwaddstr( $grid[ $j + 1 ][6], 1, 1, $targets[ $j ][ 'max' ] );
				ncurses_wrefresh( $grid[ $j + 1 ][6] );
			}

			ncurses_wclear( $grid[ $j + 1 ][3] );
			ncurses_wborder( $grid[ $j + 1 ][3], 0, 0, 0, 0, 0, 0, 0, 0 );
			ncurses_mvwaddstr( $grid[ $j + 1 ][3], 1, 1, $targets[ $j ][ 'current' ] );
			ncurses_wrefresh( $grid[ $j + 1 ][3] );
		} else {
			ncurses_wclear( $grid[ $j + 1 ][3] );
			ncurses_wborder( $grid[ $j + 1 ][3], 0, 0, 0, 0, 0, 0, 0, 0 );
			ncurses_mvwaddstr( $grid[ $j + 1 ][3], 1, 1, "N/A" );
			ncurses_wrefresh( $grid[ $j + 1 ][3] );
		}
	}
	set_time_limit( 60 );
	sleep( $interval );
}
/**************/


$pressed = ncurses_getch(); // ждём нажатия клавиши
ncurses_end(); // выходим из режима ncurses, чистим экран

?>
