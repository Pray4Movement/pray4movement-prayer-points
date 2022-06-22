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
                    <tr>
                        <th>Prayer Points</th>
                    <tr>`);
                response.forEach( function(prayer){
                    jQuery('#p4m-spinner-row').remove();
                    var tags = prayer['tags'].split(',');
                    var row = `
                        <tr>
                        <td>
                            <span class="p4m-prayer-title">
                                <span class="p4m-prayer-title-name">${prayer['title']}</span> - <i>${prayer['reference']}</i>
                                <span class="p4m-prayer-point-id">#${prayer['id']}</span>
                            </span>
                            ${prayer['content']}
                            <br>
                            <br>`;
                    if ( tags.length > 1 ) {
                        var tagRow = `<b><i>Tags: </i></b>`;
                        tags.forEach( function(tag){ tagRow += `<a href="?tag=${tag}">${tag}</a>, `;});
                        tagRow = tagRow.slice(0,-2);
                        tagRow += `<br><br>`;
                        row += tagRow;
                    }
                    row += `</td>
                        </tr>`;
                    jQuery('.p4m-libraries-table').append(row);
                });
            },
        });
    }
    loadPrayerPoints('<?php echo esc_html( $library_id ); ?>');
</script>
    <?php
}

function show_prayer_points_by_tag( $tag ) {
    ?>
    <script>
        function loadPrayerPointsByTag(tag) {
            jQuery.ajax({
                type: 'POST',
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/get_prayer_points_by_tag/${tag}`,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                },
                success: function(response) {
                    jQuery('#p4m-library-spinner').remove();
                    jQuery('.p4m-libraries-table').append(`
                        <tr>
                            <th>Prayer Points</th>
                        <tr>`);
                    response.forEach( function(prayer){
                        jQuery('#p4m-spinner-row').remove();
                        var tags = prayer['tags'].split(',');
                        var row = `
                            <tr>
                                <td>
                                <span class="p4m-prayer-title">
                                    <span class="p4m-prayer-title-name">${prayer['title']}</span> - <i>${prayer['reference']}</i>
                                    <span style="text-align:right;">#${prayer['id']}</span>
                                </span>
                                ${prayer['content']}
                                <br>
                                <br>`;
                        if ( tags.length > 1 ) {
                            var tagRow = `<b><i>Tags: </i></b>`;
                            tags.forEach( function(tag){ tagRow += `<a href="?tag=${tag}">${tag}</a>, `;});
                            tagRow = tagRow.slice(0,-2);
                            tagRow += `<br><br>`;
                            row += tagRow;
                        }
                        row += `</td>
                            </tr>`;
                        jQuery('.p4m-libraries-table').append(row);
                    });
                },
            });
        }
        loadPrayerPointsByTag('<?php echo esc_html( $tag ); ?>');
    </script>
        <?php
}

function show_prayer_libraries() {
    ?>
    <style>
        .p4m-libraries-table {
            margin: auto;
            width: 100%;
            text-align: left;
            border-collapse: separate;
        }
        .p4m-libraries-table td {
            padding: 28px;
        }
        .p4m-libraries-table td:hover {
            background-color: #f6f6f6;
            box-shadow: -4px 4px 18px lightgray;
            border-radius: 8px;
        }
        .p4m-prayer-title {
            display: block;
            font-weight: bolder;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .p4m-prayer-title-name {
            color: <?php echo esc_attr( PORCH_COLOR_SCHEME_HEX ); ?>;
        }
        .p4m-prayer-point-id {
            float: right;
            color: gray;
            font-weight: 300;
        }
    </style>
    <?php
    if ( isset( $_GET['library_id'] ) ) {
        show_prayer_points( sanitize_text_field( wp_unslash( $_GET['library_id'] ) ) );
        return;
    }

    if ( isset( $_GET['tag'] ) ) {
        show_prayer_points_by_tag( sanitize_text_field( wp_unslash( $_GET['tag'] ) ) );
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
                        <tr>
                            <th>Name</th>
                            <th>Download</th>
                        </tr>`);
                    response.forEach( function(library){
                        jQuery('#p4m-library-spinner').remove();
                        jQuery('.p4m-libraries-table').append(`
                            <tr>
                                <td><a href="?library_id=${library['id']}">${library['name']}</a></td>
                                <td><a href="javascript:downloadCSV(${library['id']}, '${library['key']}')">csv</a></td>
                            </tr>`);
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