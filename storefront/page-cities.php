<?php

$countries = get_terms( array(
    'taxonomy'   => 'countries',
    'hide_empty' => false,
) );

?>

<div class="cities-table-container">
    <h1>Cities Table</h1>

    <input type="text" id="city-search" placeholder="Search cities..." />

    <table id="cities-table" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Country</th>
                <th>City</th>
                <th>Temperature</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ( $countries as $country ) {
                $cities = get_posts( array(
                    'post_type'   => 'cities',
                    'tax_query'   => array(
                        array(
                            'taxonomy' => 'countries',
                            'field'    => 'id',
                            'terms'    => $country->term_id,
                        ),
                    ),
                    'posts_per_page' => -1,
                ) );

                foreach ( $cities as $city ) {
                    $latitude = get_post_meta( $city->ID, 'latitude', true );
                    $longitude = get_post_meta( $city->ID, 'longitude', true );
                    $weather = get_weather( $latitude, $longitude ); // Функция получения температуры через API
                    ?>
                    <tr>
                        <td><?php echo esc_html( $country->name ); ?></td>
                        <td><?php echo esc_html( $city->post_title ); ?></td>
                        <td><?php echo $weather ? $weather['temperature'] . '°C' : 'N/A'; ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

