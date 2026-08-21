$.ajaxSetup({
    cache: false,
    beforeSend: function (xhr, settings) {
        if (settings.type && settings.type.toUpperCase() === "POST" && settings.url && settings.url.indexOf("/admin/php/") !== -1) {
            xhr.setRequestHeader("X-Admin-Request", "1");
        }
    }
});
arcticmodal_settings = {
    overlay: {css: {opacity: .9}}
};
$.fn.padding = function (direction) {
    return parseFloat(this.css("padding-" + direction));
};

function windowHeight() {
    return parseFloat(window.innerHeight) || parseFloat($(window).height());
}

function windowWidth() {
    return parseFloat(window.innerWidth) || parseFloat($(window).width());
}

function arcticModalMaxHG(modal) {
    var modalParent = $(modal).parent();
    var maxHg = windowHeight() - modalParent.padding("top") - modalParent.padding("bottom");
    $(modal).css("max-height", maxHg);
}

arcticmodal_settings["beforeOpen"] = function (data, el) {
    arcticModalMaxHG(el);
};
$(window).resize(function () {
    arcticModalMaxHG(".intopModal:visible");
});

firstTime = 1;
clickCookRow = 0;
selectChange = 0;
resizeData = $.cookie("resizeData2") ? JSON.parse($.cookie("resizeData2")) : new Object();
myRowData = $.cookie("myRowData") ? JSON.parse($.cookie("myRowData")) : new Object();
pathName = location.pathname.split("/").join("").split(".php").join("").split("admin").join("a") + "d";

// Chosen
function initChosen(select) {
    select.chosen({
        disable_search_threshold: 10,
        no_results_text: "Не найдено:"
    });
}

$.datetimepicker.setLocale("ru");

function focusFirstRow(jqGridId) {
    if ($("#" + jqGridId).getDataIDs().length > 0) {
        $("#" + jqGridId).setSelection($("#" + jqGridId).getDataIDs()[0]).trigger("click");
        $("#edit_" + jqGridId + "_top").removeClass("ui-state-disabled");
        $("#del_" + jqGridId + "_top").removeClass("ui-state-disabled");
        $("#print_" + jqGridId + "_top").removeClass("ui-state-disabled");
    } else {
        $("#edit_" + jqGridId + "_top").addClass("ui-state-disabled");
        $("#del_" + jqGridId + "_top").addClass("ui-state-disabled");
        $("#print_" + jqGridId + "_top").addClass("ui-state-disabled");
    }
}

function focusRow(jqGridId) {
    $("#edit_" + jqGridId + "_top").removeClass("ui-state-disabled");
    $("#del_" + jqGridId + "_top").removeClass("ui-state-disabled");
    $("#print_" + jqGridId + "_top").removeClass("ui-state-disabled");
}

// Clicked row to cookies
function setMyRow(jqgrid, index) {
    var pageNumber = $("#" + jqgrid).jqGrid("getGridParam", "page");
    var rowNumber = $("#" + jqgrid).jqGrid("getGridParam", "rowNum");
    myRowData[pathName + jqgrid] = index;
    myRowData[pathName + jqgrid + "page"] = pageNumber;
    myRowData[pathName + jqgrid + "row"] = rowNumber;
    $.cookie("myRowData", JSON.stringify(myRowData), {
        expires: 365
    });
}

// Функция запоминания строки в гриде - jqgrid и с айди - baseID. Используется в onSelectRow в каждой табице, можно навесить на кнопку (хз, правда, зачем, но можно)
function rememberRow(jqgrid, baseID) {
    for (i = 2; i <= $("#" + jqgrid + " tbody tr").length; i++) {
        if ($("#" + jqgrid + " tbody tr:nth-child(" + i + ")").attr("id") == baseID) {
            setMyRow(jqgrid, i); // Функция записи id и номера страницы в cookies
        }
    }
}

// Функция вспоминания строки в гриде - jqgrid по данным из печенек. Нигде не используется, можно эту функцию повесить на кнопку.
function getRemRow(jqgrid) {
    $("#" + jqgrid).jqGrid("setGridParam", {
        page: myRowData[pathName + jqgrid + "page"],
        rowNum: myRowData[pathName + "jqGridrow"]
    });
    $("select.ui-pg-selbox").val(myRowData[pathName + "jqGridrow"]);
    $("#" + jqgrid + " tbody tr:nth-child(" + myRowData[pathName + jqgrid] + ")").trigger("click"); // Клик по записи из cookies
}

// Функция вспоминания строки в гриде. Используется в каждом gridComplete на страницах с одним гридом.
function jqGridRemRow() {
    if (firstTime == 1 && window.myRowData[pathName + "jqGridpage"] && window.myRowData[pathName + "jqGridrow"]) {
        $("#jqGrid").jqGrid("setGridParam", {
            page: myRowData[pathName + "jqGridpage"],
            rowNum: myRowData[pathName + "jqGridrow"]
        });
        setTimeout("$('#jqGrid').trigger('reloadGrid'); clickCookRow = 1;", 10);
    } else if (clickCookRow == 1 && window.myRowData[pathName + "jqGrid"] && window.myRowData[pathName + "jqGridrow"]) {
        $("select.ui-pg-selbox").val(myRowData[pathName + "jqGridrow"]);
        $("#jqGrid tbody tr:nth-child(" + myRowData[pathName + "jqGrid"] + ")").trigger("click"); // Клик по записи из cookies
        clickCookRow = 0;
    } else if (selectChange == 1) {
        $("#jqGrid tbody tr:nth-child(" + myRowData[pathName + "jqGrid"] + ")").trigger("click"); // Клик по записи из cookies
        selectChange = 0;
    } else {
        focusFirstRow("jqGrid");
    }
    firstTime = 0;
}

// Column width to cookies
function setMyColumns(jqgrid) {
    var cm = $("#" + jqgrid).jqGrid("getGridParam", "colModel");
    for (var i = 0; i < cm.length; i++) {
        if (cm[i].hidden == false) {
            resizeData[pathName + cm[i].name] = cm[i].width;
        }
    }
    $.cookie("resizeData2", JSON.stringify(resizeData), {
        expires: 365
    });
}

var tabsResizeTimer;

function tabsResize() {
    clearTimeout(tabsResizeTimer);
    tabsResizeTimer = setTimeout(tabsResizeImpl, 100);
}

function tabsPanelMinHeight() {
    return Math.max(400, windowHeight() - 100);
}

function tabsResizeImpl() {
    var $tabsFull = $(".g-tabs-full:visible");
    if ($tabsFull.length === 0) {
        return;
    }

    var $innerPanel = $tabsFull.children(".ui-tabs-panel:visible");
    if ($innerPanel.length === 0) {
        return;
    }

    var tabWd = Math.round(
        parseFloat($tabsFull.width())
        - parseFloat($tabsFull.children(".ui-tabs-nav").outerWidth(true))
        - parseFloat($innerPanel.css("margin-left"))
        - parseFloat($innerPanel.css("padding-left"))
        - parseFloat($innerPanel.css("border-left-width"))
        - parseFloat($innerPanel.css("margin-right"))
        - parseFloat($innerPanel.css("padding-right"))
        - parseFloat($innerPanel.css("border-right-width"))
        - 25
    );

    var $mainPanel = $(".main").children(".ui-tabs-panel:visible");
    var tabHg = windowHeight()
        - parseFloat($(".main").children(".ui-tabs-nav").outerHeight(true) || 0)
        - parseFloat($mainPanel.css("padding-top") || 0)
        - parseFloat($mainPanel.css("padding-bottom") || 0)
        - parseFloat($innerPanel.css("padding-top") || 0)
        - parseFloat($innerPanel.css("padding-bottom") || 0)
        - parseFloat($innerPanel.css("border-top-width") || 0)
        - parseFloat($innerPanel.css("border-bottom-width") || 0);

    if (!isFinite(tabWd) || tabWd <= 0 || !isFinite(tabHg) || tabHg <= 0) {
        return;
    }

    var gridHg = tabHg
        - parseFloat($innerPanel.find(".ui-jqgrid-toppager:visible").outerHeight(true) || 0)
        - parseFloat($innerPanel.find(".ui-jqgrid-hdiv:visible").outerHeight(true) || 0);

    $tabsFull.children(".ui-tabs-panel").width(tabWd).height(tabHg);

    var $grid = $innerPanel.find(".g-jqgrid");
    if ($grid.length > 0 && $grid[0].grid && isFinite(gridHg) && gridHg > 0) {
        $grid.jqGrid("setGridHeight", gridHg);
    }
}

function tabsBeforeLoad(event, ui) {
    var $panel = $(ui.panel);
    if ($panel.length) {
        $panel.css("minHeight", tabsPanelMinHeight() + "px");
    }
}

function tabsEvalPanelScripts(panel) {
    $(panel).find("script").each(function () {
        if (!this.src) {
            $.globalEval(this.text || this.textContent || this.innerHTML || "");
        }
    });
}

function initDetailTabsInPanel($panel) {
    if ($panel.find(".objects").length && !$panel.find(".objects").hasClass("ui-tabs")) {
        detailTabs(".objects");
    }
    if ($panel.find(".dicts").length && !$panel.find(".dicts").hasClass("ui-tabs")) {
        detailTabs(".dicts");
    }
}

function tabsAfterLoad(event, ui) {
    var $panel = $(ui.panel);
    if (!$panel.length) {
        return;
    }
    $panel.css("minHeight", "");
    tabsEvalPanelScripts($panel);
    initDetailTabsInPanel($panel);
    tabsResize();
    setTimeout(tabsResize, 250);
}

function modalResize() {
    $(".ui-jqdialog .FormGrid").css("height", parseFloat($(window).height()) - 150);
}

//  ----------------------------------------- FILEAPI ------------------------------------------
Array.prototype.remove = function () {
    var what,
        a = arguments,
        L = a.length,
        ax;
    while (L && this.length) {
        what = a[--L];
        while ((ax = this.indexOf(what)) !== -1) {
            this.splice(ax, 1);
        }
    }
    return this;
};

function dataInitFileFunction(el, dbName, colName, multiple, onlyPhoto, massDocs) {
    multiple = multiple || false;
    massDocs = massDocs || false;

    function addFileImg(src, name, container, nameHidden, onlyPhoto, massDocs, isEdit) {
        if ((container.find("img[src='" + src + "']").length == 0) || (!onlyPhoto)) {
            var wrap = $("<div></div>");
            wrap.addClass("jqGridFormImgContainer__wrap");

            var del = $("<div></div>");
            del.addClass("jqGridFormImgContainer__del").append("X").click(function () {
                var dImg = $(this).siblings(".jqGridFormImgContainer__img");
                var dSrc = dImg.attr("src");
                dImg.parent().remove();

                var dSrcArray = [];
                try {
                    dSrcArray = $.parseJSON($("#" + nameHidden).val());
                    dSrcArray.remove(dSrc);
                } catch (e) {
                }

                $("#" + nameHidden).val($.toJSON(dSrcArray));
            });

            var img;
            if (onlyPhoto) {
                img = $("<img />");
                img.attr("src", src).attr("alt", src).addClass("jqGridFormImgContainer__img").click(function () {
                    window.open(src, '_blank');
                });
            } else {
                img = $("<div>" + name + "</div>").data("src", src).addClass("jqGridFormImgContainer__img").click(function () {
                    window.open(src, '_blank');
                });
            }
            wrap.append(img);

            //console.log(massDocs);
            if (massDocs) {
                var name_val = name.split(".");
                name_val.pop();
                name_val = name_val.join(".");
                wrap.append('<div><input class="full" type="text" value="' + name_val + '" /></div><div><input type="text" class="group" /></div>');
            }

            wrap.append(del);

            container.append(wrap);
        } else if (!isEdit) {
            alert("Такой файл уже добавлен!");
        }
    }

    function inputFileSet(dbName, colName, multiple, parent, nameHidden, nameFile, id, onlyPhoto, massDocs) {
        $("#" + nameFile).remove();

        var file = $("<input />");
        file.attr("type", "file").attr("id", nameFile).attr("name", nameFile);
        if (multiple) {
            file.prop("multiple", true);
        }
        var fileApiSettings = {
            multiple: multiple,
            autoUpload: true,
            url: "/admin/php/file.upload.php",
            data: {
                "table": dbName,
                "col": colName,
                "id": id
            },
            maxSize: FileAPI.MB * 70,
            maxFiles: 20, /*imageTransform: {
             maxWidth: 1200,
             maxHeight: 500,
             type: "image/jpeg",
             quality: 0.6
             },*/
            onSelect: function (evt, data) {
                if (data.other.length) {
                    alert("Некорректный файл!")
                }
            },
            onFileComplete: function (evt, uiEvt) {
                //console.log(evt);
                //console.log(uiEvt);
                var cImgContainer = parent.find(".jqGridFormImgContainer");
                addFileImg(uiEvt.result, uiEvt.file.name, cImgContainer, nameHidden, onlyPhoto, massDocs);

                var cSrcArray = [];
                try {
                    cSrcArray = $.parseJSON($("#" + nameHidden).val());
                } catch (e) {
                }
                cSrcArray.push(uiEvt.result);
                $("#" + nameHidden).val($.toJSON(cSrcArray));

                inputFileSet(dbName, colName, multiple, parent, nameHidden, nameFile, id, onlyPhoto, massDocs);
            }
        };
        if (onlyPhoto) {
            fileApiSettings["accept"] = "image/*";
            fileApiSettings["imageSize"] = {
                maxWidth: 10000,
                maxHeight: 10000
            };
            fileApiSettings["maxSize"] = FileAPI.MB * 35;
        }
        file.fileapi(fileApiSettings);
        parent.append(file);
    }

    var parent = $(el).parent();
    var nameHidden = el.id;
    var nameFile = el.id + "_file";
    var id = $(el).parents(".EditTable").find("#ID_" + dbName).val();
    inputFileSet(dbName, colName, multiple, parent, nameHidden, nameFile, id, onlyPhoto, massDocs);

    var imgContainer = $("<div></div>").addClass("jqGridFormImgContainer");
    if (massDocs) {
        imgContainer.append("<div><div>Файл</div><div>Название</div><div>Группа</div><div>Действия</div></div>")
    }
    try {
        var srcArray = $.parseJSON($(el).val());
        var uniqueSrcArray = [];
        $.each(srcArray, function (i, el) {
            if ($.inArray(el, uniqueSrcArray) === -1) {
                uniqueSrcArray.push(el);
            }
        });
        $(el).val($.toJSON(uniqueSrcArray));
        for (var i = 0; i < uniqueSrcArray.length; i++) {
            addFileImg(uniqueSrcArray[i], uniqueSrcArray[i], imgContainer, nameHidden, onlyPhoto, massDocs, true);
        }
    } catch (e) {
    }

    $(el).after(imgContainer);
    $(el).addClass("i-hidden");
}

//  ------------------------------------- QUILL RICH EDITOR -------------------------------------
function get_quill_elem(value, options) {
    //console.log("get_quill_elem");
    var $div = $("<div id='" + options.id + "' class='g-quill' data-value='" + value + "'>");
    return $div[0];
}

function get_quill_value(elem, operation, value) {
    //console.log("get_quill_value " + operation);
    if ($(elem).length > 0) {
        var quill = $(elem).data("quill");
        if (operation === "get") {
            return encodeURIComponent(JSON.stringify(quill.getContents()));
        } else if (operation === "set") {
            quill.setContents(JSON.parse(decodeURIComponent(value)));
        }
    }
}

function quillGetHTML(inputDelta) {
    var tempCont = document.createElement("div");
    (new Quill(tempCont)).setContents(inputDelta);
    return tempCont.getElementsByClassName("ql-editor")[0].innerHTML;
}

function quillGetText(inputDelta) {
    var tempCont = document.createElement("div");
    var quill = new Quill(tempCont);
    quill.setContents(inputDelta);
    return quill.getText();
}

function numberFormatter(cellvalue, options, rowObject) {
    var newValue = "";
    if (cellvalue) {
        newValue = (cellvalue * 1).toString();
    }
    return newValue;
}

function photosFormatter(cellvalue, options, rowObject) {
    var newValue = "";
    if (cellvalue) {
        newValue = '<span class="g-hidden">' + cellvalue + '</span>';
        newValue = '<img alt="Фото" class="icon__photo" src="/admin/img/picture.png" />' + newValue;
        /*var photos = JSON.parse(cellvalue);
         for (var i = 0; i < photos.length; i++) {
         }*/
    }
    return newValue;
}

$(document).ready(function () {
    //  ------------------------------------------ LOGOUT ------------------------------------------
    $(".admin__exit").click(function () {
        $.ajax({
            url: "/admin/php/logout.php",
            complete: function () {
                location.reload();
            }
        })
    });

    //  ------------------------------------------- ТАБЫ -------------------------------------------
    $(".main").tabs({
        beforeLoad: tabsBeforeLoad,
        load: tabsAfterLoad,
        activate: function () {
            tabsResize();
        }
    });
    $(window).resize(tabsResize);

    $(window).resize(modalResize);

    $(".admin__my").click(function () {
        var onlymy = "0";
        if ($(this).prop("checked")) {
            onlymy = 1;
        }
        $.ajax({
            url: "/admin/php/onlymy.php",
            data: {
                onlymy: onlymy
            },
            method: "POST",
            success: function () {
                var current_index = $(".objects").tabs("option", "active");
                $(".objects").tabs('load', current_index);
            }
        });
    });
});

//  ------------------------------------------- ДЛЯ ТАБОВ -------------------------------------------
function hideLeftTabs() {
    $(".objects, .dicts").find(".ui-tabs-anchor").each(function() {
        if (!$(this).data("hide")) {
            var text = $(this).text();
            $(this).data("hide", text);
            $(this).html(text.substring(0,1));
            $(".g-tabs-hide").data("ishidden", "true").text("→");
            tabsResize();
        }
    });
}
function showLeftTabs() {
    $(".objects, .dicts").find(".ui-tabs-anchor").each(function() {
        if ($(this).data("hide")) {
            $(this).html($(this).data("hide"));
            $(this).data("hide", "");
            $(".g-tabs-hide").data("ishidden", "").text("←");
            tabsResize();
        }
    });
}

var firstTimeTabs = true;
function detailTabs(selector) {
    $(selector).tabs({
        beforeLoad: tabsBeforeLoad,
        load: function (event, ui) {
            if ((windowWidth() < 1000) && (firstTimeTabs)) {
                hideLeftTabs();
                firstTimeTabs = false;
            }
            tabsAfterLoad(event, ui);
        },
        activate: function () {
            tabsResize();
        }
    }).addClass("ui-tabs-vertical ui-helper-clearfix");
}
$(document).ready(function() {
    $(document).on("click", ".g-tabs-hide", function() {
        if ($(this).data("ishidden")) {
            showLeftTabs();
        } else {
            hideLeftTabs();
        }
    });
});