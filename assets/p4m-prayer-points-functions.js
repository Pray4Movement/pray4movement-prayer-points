function loadLibraries() {
    jQuery('#p4m-table-heading').text('Prayer Libraries');
    var librariesTable = `
    <table class="p4m-libraries-table">
        <tr class="p4m-translate-row">
            <td colspan="4">
                <select id="languages_dropdown" onchange="javascript:changeLanguage(this);">
                    <option value="all">All Languages</option>
                    <option value="en">🇺🇸 English</option>
                    <option value="es">🇪🇸 Spanish</option>
                    <option value="fr">🇫🇷 French</option>
                    <option value="pt">🇧🇷 Portuguese</option>
                </select>
            </td>
        </tr>
        <tr id="p4m-library-spinner">
            <td colspan="2">
                <i>loading...</i>
            </td>
        </tr>
    </table>`;
    jQuery('#p4m-content').append(librariesTable);
    var language = '';
    try {
        language = new RegExp('lang\?=(.+?)$').exec(window.location['href'])[1];
    }catch(error){}
    if ( language !== '' ) {
        get_libraries_by_language(language);
        return;
    }
    get_parent_libraries();
}

function get_parent_libraries() {
    jQuery.ajax({
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        url: window.location.origin + '/wp-json/pray4movement-prayer-points/v1/get_prayer_libraries',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', p4mPrayerPoints.nonce);
        },
        success: function(response) {
            jQuery('#p4m-library-spinner').remove();
            jQuery('.p4m-libraries-table').append(`
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Languages</th>
                    <th>Action</th>
                </tr>`);
            response.forEach( function(library){
                var isParent = true;
                if ( library['parent_id'] > 0 ) {
                    isParent = false;
                }
                if ( isParent ) {
                    jQuery('.p4m-libraries-table').append(`
                        <tr>
                            <td><a href="?view_library_id=${library['id']}">${library['name']}</a></td>
                            <td>${library['description']}</td>
                            <td id="p4m-row-parent-id-${library['id']}"></td>
                            <td>
                                <a href="?download_library_id=${library['id']}">Download</a>
                            </td>
                        </tr>`);
                } else {
                    jQuery(`#p4m-row-parent-id-${library['parent_id']}`).append(`<a href="?view_library_id=${library['id']}">` + getFlag(library['language']) + `</a>`);
                }
            });
        },
    });
}

function get_libraries_by_language( language ) {
    removeHeaderBlock();
    jQuery.ajax({
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/get_prayer_libraries_by_language/${language}`,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', p4mPrayerPoints.nonce);
        },
        success: function(response) {
            jQuery('#p4m-library-spinner').remove();
            jQuery('.p4m-libraries-table').append(`
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Download</th>
                </tr>`);
            response.forEach( function(library){
            jQuery('.p4m-libraries-table').append(`
                <tr>
                    <td><a href="?view_library_id=${library['id']}">${library['name']}</a></td>
                    <td>${library['description']}</td>
                    <td><a href="/?download_library_id=${library['id']}">Download</a></td>
                </tr>`);
            jQuery(`#languages_dropdown option[value="${language}"]`).attr("selected", "selected");
            });
        },
    });
}

function changeLanguage(currentElement) {
    var language = currentElement.value;
    if ( language !== 'all' ) {
        window.location['href'] = '?lang=' + language;
        return;
    }
    window.location['href'] = '.';
}

function getFlag(language) {
    var flags = {
        'en':'🇺🇸',
        'es':'🇪🇸',
        'fr':'🇫🇷',
        'pt':'🇧🇷',

    };
    return flags[language];
}

function loadPrayerPoints() {
    removeHeaderBlock();

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
        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/get_prayer_points_localized/` + p4mPrayerPoints.libraryId,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', p4mPrayerPoints.nonce );
        },
        success: function(response) {
            jQuery('#p4m-library-spinner').remove();
            jQuery('.p4m-prayer-points-table').append(`<br>`);
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
                            <span class="p4m-prayer-title-name">${prayer['title']}</span>`;
                if ( prayer['reference'] ) {
                    row += ` - <i>${prayer['reference']}</i>`;
                }
                row += `
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
            jQuery('#p4m-content').before(`
            <h1 class="p4m-library-name">${p4mPrayerPoints.libraryName}</h1>
                <span class="export-buttons" style="max-width: 100%;">
                    <div>
                        <a href="/?download_library_id=${p4mPrayerPoints.libraryId}">Download</a>
                    </div>
                </span>`);
        },
    });
}

function removeHeaderBlock() {
    jQuery('.wp-block-cover').remove();
}

function loadPrayerPointsByTag() {
    removeHeaderBlock();
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
            jQuery('#p4m-content').prepend(`<h1 class="p4m-library-name">Tag - ${p4mPrayerPoints.tag}</h1>`);
            jQuery('#p4m-library-spinner').remove();
            jQuery('.p4m-prayer-points-table').append(`
                <tr>
                    <th>Prayer Points</th> //foobar
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
                    tags.forEach( function(tag){ tagRow += `<a href="?prayer_tag=${tag}">${tag}</a>, `;});
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

function loadLibraryRules() {
    removeHeaderBlock();
    if ( typeof p4mPrayerPoints.libraryName == 'undefined' ) {
        jQuery('#p4m-content').prepend(`
            <h1 class="p4m-library-name">Whoops! Library not found</h1>
            <div class="p4m-back-link-div">
                <a href="javascript:history.back();"><< back</a>
            </div>
            `);
        return;
    }
    
    jQuery('#p4m-content').prepend(`<h1 class="p4m-library-name">Download - ${p4mPrayerPoints.libraryName}</h1>`);
    var prayerPointsTable = `
    <table class="p4m-localization-rules-table">
        <tr id="p4m-library-spinner">
            <td style="text-align: center;">
                <i>Downloading...</i>
            </td>
        </tr>
    </table>`;
    jQuery('#p4m-content').append(prayerPointsTable);
    jQuery('#p4m-library-spinner').remove();
    if ( !jQuery.isEmptyObject(p4mPrayerPoints.rules) ) {
        p4mPrayerPoints.rules.forEach(function(rule){
            var exampleRow = 'No example available';
            if (rule.example_from){
                exampleRow = `${rule.example_from} → ${rule.example_to}`;
            }
            jQuery('.p4m-localization-rules-table').append(`
            <tr id="p4m-localization-row-rule-${rule.id}">
                <td>
                    <b>${rule.replace_from} → ${rule.replace_to}</b>
                    <br>
                    <i>${exampleRow}</i>
                </td>
                <td style="text-align:center;">
                    ${rule.replace_from} →
                </td>
                <td>
                    <input type="text" id="p4m-replace-rule-to-${rule.id}" value="${rule.replace_to}">
                </td>
            </tr>
            `);
        });   
    }
    var prayerPointsDownloadRow = `
    <tr>
        <td colspan="3">
            <a href="javascript:downloadCSV(${p4mPrayerPoints.libraryId}, '${p4mPrayerPoints.libraryKey}');" class="button" style="display: block; margin: auto;">Download CSV</a>
        </td>
    </tr>
    `;
    jQuery('.p4m-localization-rules-table').append(prayerPointsDownloadRow)
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
            
            if ( !jQuery.isEmptyObject(p4mPrayerPoints.rules) ) {
                p4mPrayerPoints.rules.forEach(function(rule) {
                    var regexRule = new RegExp(rule.replace_from, 'g');
                    var replaceTo = jQuery(`#p4m-replace-rule-to-${rule.id}`).val();
                    output = output.replace(regexRule, replaceTo);
                });
            }

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

function updateLocalizationRule(ruleId) {
    var replaceFrom = jQuery(`#p4m-replace-rule-from-${ruleId}`)[0].value;
    var replaceTo = jQuery(`#p4m-replace-rule-to-${ruleId}`)[0].value;
    jQuery.ajax({
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/update_localization_rule/${ruleId}/${replaceFrom}/${replaceTo}`,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', p4mPrayerPoints.nonce);
        },
        complete: function() {
            //jQuery(`#p4m-localization-row-rule-${ruleId}`).remove();
        },
    });
}