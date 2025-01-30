jQuery(document).ready(function($) {
    $('#city-search').on('input', function() {
        var searchTerm = $(this).val();

        if (searchTerm.length < 3) {
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'city_search',
                search_term: searchTerm
            },
            success: function(response) {
                if (response.success) {
                    var rows = '';
                    $.each(response.data, function(index, city) {
                        rows += '<tr><td>' + city.country + '</td><td>' + city.city + '</td><td>' + city.temperature + '</td></tr>';
                    });
                    $('#cities-table tbody').html(rows);
                } else {
                    $('#cities-table tbody').html('<tr><td>No cities found.</td></tr>');
                }
            }
        });
    });
});
