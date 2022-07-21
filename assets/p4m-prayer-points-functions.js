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
                    <th>Download</th>
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
                                <a href="?download_library_id=${library['id']}">download</a>
                                <a href="javascript:displayLocalizationDownload(${library['id']}, '${library['name']}', '${library['key']}')">csv</a>
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
                    <td><a href="javascript:displayLocalizationDownload(${library['id']}, '${library['name']}', '${library['key']}')">csv</a></td>
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
    displayLocalizationInputs();

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
            jQuery('.p4m-localization-box').before(`<h2 class="p4m-library-name">${p4mPrayerPoints.libraryName}</h2>`);
            jQuery('.p4m-localization-box').after(`<span class="export-buttons"><a href="javascript:downloadCSV(${p4mPrayerPoints.libraryId}, '${p4mPrayerPoints.libraryKey}');">csv</a></span>`);
        },
    });
}

function displayLocalizationInputs() {
    var localizationInputs = `
    <div class="p4m-localization-box">
        <div class="p4m-localization-box-title">
            Hit close to home!
            <br>
            Localize these prayer points below.
        </div>
        <div>
            <label class="p4m-localization-box-label">Location:</label> <input type="text" id="p4m-localization-location" placeholder="the world">
        </div>
        <div>
            <label class="p4m-localization-box-label">People Group:</label><input type="text" id="p4m-localization-people-group" placeholder="people">
        </div>
        <div>
            <a class="button p4m-localization-box-button" id="update-prayer-points" href="javascript:updateLocalization();">Update</a>
        </div>
    </div>`;
    jQuery('#p4m-content').append(localizationInputs);
}

function displayLocalizationDownload( libraryID, libraryName, libraryKey ) {
    var localizationInputs = `
    <div class="p4m-download-modal">
        <div class="p4m-modal-box">
            <span class="modal-close" onclick="jQuery('.p4m-download-modal').css('display', 'none');">&times;</span>
            <div class="p4m-localization-box-title">
                Localize the prayer points for
                <br>
                '${libraryName}'
            </div>
            <div>
                <label class="p4m-localization-box-label">Location:</label> <input type="text" id="p4m-localization-location" placeholder="the world">
            </div>
            <div>
                <label class="p4m-localization-box-label">People Group:</label><input type="text" id="p4m-localization-people-group" placeholder="people">
            </div>
            <div>
                <a class="button p4m-localization-box-button" id="update-prayer-points" href="javascript:downloadCSV(${libraryID}, '${libraryKey}');">Download</a>
            </div>
        </div>
    </div>`;
    jQuery('#p4m-content').append(localizationInputs);
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
    var prayerPointsTable = `
    <table class="p4m-localization-rules-table">
        <tr id="p4m-library-spinner">
            <td style="text-align: center;">
                <i>Downloading...</i>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <th colspan="4">Add new rule</th>
        </tr>
        <tr>
            <td>
                <input type="text" placeholder="from" id="new-rule-from">
            </td>
            <td>
                <input type="text" placeholder="to" id="new-rule-to">
            </td>
            <td><a href="javascript:void(0)" onclick="javascript:addLocalizationRule();" class="button" id="add-new-rule">save</a></td>
        </tr>
    </table>`;
    jQuery('#p4m-content').append(prayerPointsTable);
    p4mPrayerPoints.rules.forEach(function(rule){
        var exampleRow = 'No example available';
        var replaceFrom = jQuery(`#p4m-localization-replace-rule-from-${rule.rule_id}`);
        var replaceTo = jQuery(`#p4m-localization-replace-rule-to-${rule.rule_id}`);
        if (rule.example_from){
            exampleRow = `${rule.example_from} → ${rule.example_to}`;
        }
        jQuery('#p4m-library-spinner').remove();
        jQuery('.p4m-localization-rules-table').append(`
        <tr id="p4m-localization-row-rule-${rule.rule_id}">
            <td>
                <b>${rule.replace_from} → ${rule.replace_to}</b>
                <br>
                <i>${exampleRow}</i>
            </td>
            <td>
                <input id="p4m-replace-rule-from-${rule.rule_id}" type="text" value="${rule.replace_from}">
            </td>
            <td>
                <input type="text" id="p4m-replace-rule-to-${rule.rule_id}" value="${rule.replace_to}">
            </td>
            <td>
                <a class="button" href="javascript:updateLocalizationRule(${rule.rule_id});">update</a>
                <a href="javascript:deleteLocalizationRule(${rule.rule_id});" class="button"  style="background-color:#b32d2e;">delete</a>
            </td>
        </tr>
        `);
    });
}

function updateLocalization() {
    var location = jQuery('#p4m-localization-location').val();
    var people_group = jQuery('#p4m-localization-people-group').val();
    jQuery('.p4m-location').text(location);
    jQuery('.p4m-people-group').text(people_group);
}

function downloadCSV( libraryId, fileName='pray4movement_prayer_library_download' ) {
    var p4mLocation = jQuery('#p4m-localization-location')[0].value;
    var p4mPeopleGroup = jQuery('#p4m-localization-people-group')[0].value;
    if (p4mLocation === ''){
        p4mLocation = 'the World';
    }

    if (p4mPeopleGroup === ''){
        p4mPeopleGroup = 'people';
    }
    var peopleGroup = jQuery('#p4m-localization-people-group')[0].value;
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
            output = output.replace(/XXX/g, p4mLocation);
            output = output.replace(/YYY/g, p4mPeopleGroup);
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

function addLocalizationRule() {
    var libraryId = p4mPrayerPoints.libraryId;
    var replaceFrom = jQuery('#new-rule-from')[0].value;
    var replaceTo = jQuery('#new-rule-to')[0].value;
    var userId = p4mPrayerPoints.rules[0].user_id;
    jQuery.ajax({
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/add_localization_rule/${libraryId}/${replaceFrom}/${replaceTo}/${userId}`,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', p4mPrayerPoints.nonce);
        },
        complete: function() {
            jQuery('.p4m-localization-rules-table').append(`
                <tr id="p4m-localization-row-rule-${rule_id}">
                    <td>
                        <b>${replaceFrom} → ${replaceTo}</b>
                        <br>
                        <i>Check back for an example</i>
                    </td>
                    <td>
                        <input type="text" value="${replaceTo}">
                    </td>
                    <td>
                        <a class="button">update</a>
                    </td>
                </tr>`);
        },
    });
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

function deleteLocalizationRule(ruleId) {
    jQuery.ajax({
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        url: window.location.origin + `/wp-json/pray4movement-prayer-points/v1/delete_localization_rule/${ruleId}`,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', p4mPrayerPoints.nonce);
        },
        complete: function() {
            jQuery(`#p4m-localization-row-rule-${ruleId}`).remove();
        },
    });
}