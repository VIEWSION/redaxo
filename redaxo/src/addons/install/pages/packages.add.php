<?php

/** @var rex_addon $this */

$addonkey = rex_request('addonkey', 'string');
$addons = [];

echo rex_api_function::getMessage();

try {
    $addons = rex_install_packages::getAddPackages();
} catch (rex_functional_exception $e) {
    echo rex_view::warning($e->getMessage());
    $addonkey = '';
}

if ($addonkey && isset($addons[$addonkey]) && !rex_addon::exists($addonkey)) {
    $addon = $addons[$addonkey];

    $content = '
        <table class="table">
            <tbody>
            <tr>
                <th class="rex-table-width-5">' . $this->i18n('name') . '</th>
                <td data-title="' . $this->i18n('name') . '">' . $addon['name'] . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('author') . '</th>
                <td data-title="' . $this->i18n('author') . '">' . $addon['author'] . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('shortdescription') . '</th>
                <td data-title="' . $this->i18n('shortdescription') . '">' . nl2br($addon['shortdescription']) . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('description') . '</th>
                <td data-title="' . $this->i18n('description') . '">' . nl2br($addon['description']) . '</td>
            </tr>
            </tbody>
        </table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', '<b>' . $addonkey . '</b> ' . $this->i18n('information'), false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;

    $content = '
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon"></th>
                <th class="rex-table-width-4">' . $this->i18n('version') . '</th>
                <th>' . $this->i18n('description') . '</th>
                <th class="rex-table-action">' . $this->i18n('header_function') . '</th>
            </tr>
            </thead>
            <tbody>';

    foreach ($addon['files'] as $fileId => $file) {
        $file['description'] = trim($file['description']) == '' ? '&nbsp;' : $file['description'];

        $content .= '
            <tr>
                <td class="rex-table-icon"><i class="rex-icon rex-icon-package"></i></td>
                <td data-title="' . $this->i18n('version') . '">' . $file['version'] . '</td>
                <td data-title="' . $this->i18n('description') . '">' . nl2br($file['description']) . '</td>
                <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'rex-api-call' => 'install_package_add', 'file' => $fileId]) . '" data-pjax="false"><i class="rex-icon rex-icon-download"></i> ' . $this->i18n('download') . '</a></td>
            </tr>';
    }

    $content .= '</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('files'), false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
} else {
    $toolbar = '
    <div class="navbar-form">
        <div class="form-group">
            <div class="input-group" id="rex-js-install-addon-search">
                <input class="form-control" type="text" placeholder="' . $this->i18n('search') . '" />
                <span class="input-group-btn"><button class="btn btn-default">' . $this->i18n('clear') . '</button></span>
            </div>
        </div>
    </div>
    ';

    $content = '
        <table class="table table-striped table-hover" id="rex-js-table-install-packages-addons">
         <thead>
            <tr>
                <th class="rex-table-icon"><a href="' . rex_url::currentBackendPage(['func' => 'reload']) . '" title="' . $this->i18n('reload') . '"><i class="rex-icon rex-icon-refresh"></i></a></th>
                <th>' . $this->i18n('key') . '</th>
                <th>' . $this->i18n('name') . ' / ' . $this->i18n('author') . '</th>
                <th>' . $this->i18n('shortdescription') . '</th>
                <th class="rex-table-action">' . $this->i18n('header_function') . '</th>
            </tr>
         </thead>
         <tbody>';

    foreach ($addons as $key => $addon) {
        if (rex_addon::exists($key)) {
            $content .= '
                <tr>
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-package"></i></td>
                    <td data-title="' . $this->i18n('key') . '">' . $key . '</td>
                    <td data-title="' . $this->i18n('name') . '"><b>' . $addon['name'] . '</b><br /><span class="text-muted">' . $addon['author'] . '</span></td>
                    <td data-title="' . $this->i18n('shortdescription') . '">' . nl2br($addon['shortdescription']) . '</td>
                    <td class="rex-table-action"><span class="text-nowrap"><i class="rex-icon rex-icon-package-exists"></i> ' . $this->i18n('addon_already_exists') . '</span></td>
                </tr>';
        } else {
            $url = rex_url::currentBackendPage(['addonkey' => $key]);
            $content .= '
                <tr>
                    <td class="rex-table-icon"><a href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                    <td data-title="' . $this->i18n('key') . '"><a href="' . $url . '">' . $key . '</a></td>
                    <td data-title="' . $this->i18n('name') . '"><b>' . $addon['name'] . '</b><br /><span class="text-muted">' . $addon['author'] . '</span></td>
                    <td data-title="' . $this->i18n('shortdescription') . '">' . nl2br($addon['shortdescription']) . '</td>
                    <td class="rex-table-action"><a href="' . $url . '"><i class="rex-icon rex-icon-view"></i> ' . rex_i18n::msg('view') . '</a></td>
                </tr>';
        }
    }

    $content .= '</tbody></table>';

    $content .= '
        <script type="text/javascript">
        <!--
        jQuery(function($) {
            var table = $("#rex-js-table-install-packages-addons");
            var tablebody = table.find("tbody");
            var replaceNumber = function replaceNumber() {
                table.prev().find(".panel-title").text(
                function(i,txt) {
                    return txt.replace(/\d+/, tablebody.find("tr").filter(":visible").length);
                });
            };
            $("#rex-js-install-addon-search .form-control").keyup(function () {
                table.find("tr").show();
                var search = $(this).val().toLowerCase();
                if (search) {
                    table.find("tbody tr").each(function () {
                        var tr = $(this);
                        if (tr.text().toLowerCase().indexOf(search) < 0) {
                            tr.hide();
                        }
                    });
                    replaceNumber();
                }
                else
                {
                    replaceNumber();
                }
            });
            $("#rex-js-install-addon-search .btn").click(function () {
                $("#rex-js-install-addon-search .form-control").val("").trigger("keyup");
            });
        });
        //-->
        </script>
    ';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('addons_found', count($addons)), false);
    $fragment->setVar('options', $toolbar, false);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
}
