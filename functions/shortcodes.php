<?php
add_shortcode( 'p4m_prayer_libraries', 'show_prayer_libraries' );

function show_prayer_libraries() {

    ?>
    <style>
        .p4m-libraries-table {
            margin: auto;
            width: 75%;
            text-align: center;
        }
    </style>
    <script>
        function loadLibraries() {
            jQuery.ajax({
                            type: 'POST',
                            contentType: 'application/json; charset=utf-8',
                            dataType: 'json',
                            url: window.location.origin + '/wp-json/pray4movement-prayer-points/v1/get_prayer_libraries',
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                            },
                            success: function(response) {
                                response.forEach( function(library){
                                    console.log(library);
                                    jQuery('#p4m-spinner-row').remove();
                                    jQuery('.p4m-libraries-table').append(`
                                    <tr>
                                        <td><a href="#">${library['name']}</a></td>
                                        <td><a href="javascript:downloadCSV(${library['id']}, '${library['key']}')">csv</a></td>
                                    </tr>
                                    `);
                                });
                            },
                        });
        }

        function downloadCSV( libraryId, fileName='pray4movement_prayer_library_download' ) {
            jQuery.ajax( {
                    type: 'POST',
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'json',
                    url: window.location.origin + '/wp-json/pray4movement-prayer-points/v1/get_prayer_points/' + libraryId,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                    },
                    success: function(response) {
                        var columnsAlreadyDisplayed = false;
                        let output = "data:text/csv;charset=utf-8,";
                            var columnNames = Object.keys(response[0]);
                            if (columnsAlreadyDisplayed){
                                columnNames.forEach( function(column) {
                                    output += `"` + column + `",`;
                                } )
                                output = output.slice(0,-1);
                                output += `\r\n`;
                                columnsAlreadyDisplayed = true;
                            }
                            response.forEach( function(row){
                                columnNames.forEach( function( columnName ) {
                                    output += `"${row[columnName]}",`;
                                } )
                            output = output.slice(0,-1);
                            output += `\r\n`;
                        } );
                        var encodedUri = encodeURI(output);
                        var downloadLink = document.createElement('a');
                        downloadLink.href = encodedUri;
                        downloadLink.download = `pray4movement_prayer_library_${fileName}.csv`;
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                    }
                } );
        }
        loadLibraries();
    </script>
    <?php
}