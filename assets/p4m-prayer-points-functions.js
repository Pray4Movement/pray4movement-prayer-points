function loadLibraries() {
    var librariesTable = `
    <table class="p4m-libraries-table">
        <tr id="p4m-library-spinner">
            <td colspan="2">
                <i>loading...</i>
            </td>
        </tr>
    </table>`;
    jQuery('#p4m-content').append(librariesTable);
    jQuery.ajax({
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        url: window.location.origin + '/wp-json/pray4movement-prayer-points/v1/get_prayer_libraries',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', p4mPrayerPoints.nonce );
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

function loadPrayerPoints() {
    var localizationInputs = `
    <div class="p4m-localization-box">
        <div class="p4m-localization-box-title">
            Hit close to home!
            <br>
            Localize these prayer points below.
        </div>
        <div>
            <label>Location: </label> <input type="text" id="p4m-localization-location" placeholder="the world">
        </div>
        <div>
            <label>People Groups: </label><input type="text" id="p4m-localization-people-group" placeholder="people">
        </div>
        <div>
            <a class="btn btn-common btn-rm" id="update-prayer-points" href="javascript:updateLocalization();">Update</a>
        </div>
    </div>`;
    jQuery('#p4m-content').append(localizationInputs);

    var prayerPointsTable = `
    <table class="p4m-prayer-points-table">
        <tr id="p4m-library-spinner">
            <td colspan="2">
                <i>loading...</i>
            </td>
        </tr>
    </table>`;
    jQuery('#p4m-content').append(prayerPointsTable);

    jQuery.ajax({
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/get_prayer_points/` + p4mPrayerPoints.libraryId,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', p4mPrayerPoints.nonce );
        },
        success: function(response) {
            jQuery('#p4m-library-spinner').remove();
            jQuery('.p4m-prayer-points-table').append(`
                <tr>
                    <th>Prayer Points</th>
                <tr>`);
            response.forEach( function(prayer){
                jQuery('#p4m-spinner-row').remove();
                var tags = prayer['tags'].split(',');
                prayer['title'] = prayer['title'].replace(/XXX/g, '<span class="p4m-location">XXX</span>');
                prayer['title'] = prayer['title'].replace(/YYY/g, '<span class="p4m-people-group">YYY</span>');
                prayer['content'] = prayer['content'].replace(/XXX/g, '<span class="p4m-location">XXX</span>');
                prayer['content'] = prayer['content'].replace(/YYY/g, '<span class="p4m-people-group">YYY</span>');
                var row = `
                    <tr>
                    <td>
                        <span class="p4m-prayer-title">
                            <span class="p4m-prayer-title-name">${prayer['title']}</span> - <i>${prayer['reference']}</i>
                            <span class="p4m-prayer-point-id">#${prayer['id']}</span>
                        </span>
                        <span class="p4m-prayer-point-content">
                            ${prayer['content']}
                        </span>
                        <br>
                        <br>`;
                if ( tags.length > 1 ) {
                    var tagRow = `<span class="p4m-prayer-tag">tags: </span>`;
                    tags.forEach( function(tag){
                        tag = jQuery.trim(tag);
                        tagRow += `<a href="?prayer_tag=${tag}">${tag}</a>, `;
                    });
                    tagRow = tagRow.slice(0,-2);
                    tagRow += `<br><br>`;
                    row += tagRow;
                }
                row += `</td>
                    </tr>`;
                jQuery('.p4m-prayer-points-table').append(row);
            });
        },
    });
}

function loadPrayerPointsByTag() {
    var prayerPointsTable = `
    <table class="p4m-prayer-points-table">
        <tr id="p4m-library-spinner">
            <td colspan="2">
                <i>loading...</i>
            </td>
        </tr>
    </table>`;
    jQuery('#p4m-content').append(prayerPointsTable);
    jQuery.ajax({
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/get_prayer_points_by_tag/${p4mPrayerPoints.tag}`,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', p4mPrayerPoints.nonce );
        },
        success: function(response) {
            jQuery('#p4m-library-spinner').remove();
            jQuery('.p4m-prayer-points-table').append(`
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
                jQuery('.p4m-prayer-points-table').append(row);
            });
        },
    });
}

function updateLocalization() {
    var location = jQuery('#p4m-localization-location').val();
    var people_group = jQuery('#p4m-localization-people-group').val();
    jQuery('.p4m-location').text(location);
    jQuery('.p4m-people-group').text(people_group);
}

function downloadCSV( libraryId, fileName='pray4movement_prayer_library_download' ) {
    jQuery.ajax( {
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/get_prayer_points/${libraryId}`,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', p4mPrayerPoints.nonce );
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