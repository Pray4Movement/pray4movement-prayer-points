<?php
add_shortcode( 'p4m_prayer_libraries', 'show_prayer_libraries' );

function show_prayer_points( $library_id ) {
    ?>
<script>
    function loadPrayerPoints(libraryId) {
        jQuery.ajax({
            type: 'POST',
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/get_prayer_points/${libraryId}`,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
            },
            success: function(response) {
                jQuery('#p4m-library-spinner').remove();
                jQuery('.p4m-libraries-table').append(`
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Reference</th>
                        <th>Content</th>
                        <th>Tags</th>
                    <tr>
                </thead>
                `);
                response.forEach( function(prayer){
                    jQuery('#p4m-spinner-row').remove();
                    jQuery('.p4m-libraries-table').append(`
                    <tbody>
                        <tr>
                            <td>${prayer['id']}</td>
                            <td>${prayer['title']}</td>
                            <td>${prayer['reference']}</td>
                            <td>${prayer['content']}</td>
                            <td>${prayer['tags']}</td>
                        </tr>
                    </tbody>
                    `);
                });
            },
        });
    }
    loadPrayerPoints('<?php echo esc_html( $library_id ); ?>');
</script>
    <?php
}

function show_prayer_libraries() {
    ?>
    <style>
        .p4m-libraries-table {
            margin: auto;
            width: 75%;
            text-align: center;
        }
    </style>
    <?php
    if ( isset( $_GET['library_id'] ) ) {
        show_prayer_points( sanitize_text_field( wp_unslash( $_GET['library_id'] ) ) );
        return;
    }
    ?>
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
                    jQuery('.p4m-libraries-table').append(`
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    `);
                    response.forEach( function(library){
                        jQuery('#p4m-library-spinner').remove();
                        jQuery('.p4m-libraries-table').append(`
                        <tbody>
                            <tr>
                                <td><a href="?library_id=${library['id']}">${library['name']}</a></td>
                                <td><a href="javascript:downloadCSV(${library['id']}, '${library['key']}')">csv</a></td>
                            </tr>
                        </tbody>
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