<?php
/**
 * @author Anastasia Sidak <m0st1ce.nastya@gmail.com>
 *
 * @link https://steamcommunity.com/profiles/76561198038416053
 * @link https://github.com/M0st1ce
 *
 * @license GNU General Public License Version 3
 */

// Получаем кэш
$data['module_block_main_top'] = $Modules->get_module_cache('module_block_main_top');

// Если кэш морально устарел, то думаю его нужно обновить
if ( ( $data['module_block_main_top'] == '' ) || ( time() > $data['module_block_main_top']['time'] ) ) {

    unset( $data['module_block_main_top'] );
    
    // Обновляем время последнего кэширования.
    $data['module_block_main_top']['time'] = time() + $Modules->array_modules['module_block_main_top']['setting']['cache_time'];

    // Проверка на подключенный мод - Levels Ranks
    if ( ! empty( $Db->db_data['LevelsRanks'] ) ):
        for ($d = 0; $d < $Db->table_count['LevelsRanks']; $d++ ):
            // Забираем массив даннхы
            $data['module_block_main_top'][] = $Db->queryAll( 'LevelsRanks', $Db->db_data['LevelsRanks'][$d]['USER_ID'], $Db->db_data['LevelsRanks'][$d]['DB_num'],'SELECT name, rank, steam, playtime, value, kills, deaths, CASE WHEN deaths = 0 THEN deaths = 1 END, TRUNCATE( kills/deaths, 2 ) AS kd FROM ' . $Db->db_data['LevelsRanks'][ $d ]['Table'] . ' order by `value` desc LIMIT 10' );
        endfor;
    endif;

    // Проверка на подключенный мод - FPS
    if ( ! empty( $Db->db_data['FPS'] ) ):
        for ($d = 1; $d <= $Db->table_count['FPS']; $d++ ):
            // Забираем массив даннхы
            $data['module_block_main_top'][] = $Db->queryAll( 'FPS', 0, 0,
                                                       'SELECT fps_players.nickname AS name,
                                                        fps_players.steam_id AS steam, 
                                                        fps_servers_stats.points AS value, 
                                                        fps_servers_stats.kills, 
                                                        fps_servers_stats.deaths, 
                                                        fps_servers_stats.playtime,
                                                        ( SELECT fps_ranks.id
                                                          FROM fps_ranks 
                                                          WHERE fps_ranks.rank_id = ' . $Db->db_data['FPS'][ $d-1 ]['ranks_id'] .' 
                                                          AND fps_ranks.points <= fps_servers_stats.points 
                                                          ORDER BY fps_ranks.points DESC LIMIT 1
                                                        ) AS rank,
                                                        CASE WHEN fps_servers_stats.deaths = 0 THEN fps_servers_stats.deaths = 1 END, TRUNCATE( fps_servers_stats.kills/fps_servers_stats.deaths, 2 ) AS kd
                                                        FROM fps_players
                                                        INNER JOIN fps_servers_stats ON fps_players.account_id = fps_servers_stats.account_id
                                                        WHERE fps_servers_stats.server_id = ' . $d . '
                                                        order by `value` desc LIMIT 10' );
        endfor;
    endif;

    ! file_exists( MODULES_SESSIONS . 'module_block_main_top' ) && mkdir( MODULES_SESSIONS . 'module_block_main_top', 0777, true );

    // Обновляем кэш
    $Modules->set_module_cache( 'module_block_main_top', $data['module_block_main_top'] );
}