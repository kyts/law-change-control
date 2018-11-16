define("WorkItems", ["ko"], function (ko) {
    var options = null;
    var viewModel = null;

    function init(opt) {
        options = opt;

        viewModel = {
            workItems: ko.observableArray(),
            formatDate: formatDate,
            showIteam: showIteam
        };

        getWorkItems();

        ko.applyBindings(viewModel, document.getElementById(options.workItemsContainerId));
    }

    function getWorkItems() {
        $.ajax({
            url: options.workItemsUrl,
            type: "GET",
            success: function (result) {
                if (result.workItems) {
                    viewModel.workItems(result.workItems);
                }
            }
        });
    }

    function formatDate(jsonDate) {
        return new Date(parseInt(jsonDate.substr(6))).toLocaleDateString();
    }

    function showIteam(workItem) {
        window.open(options.iteamUrl + "/" + workItem.Id);
    }


    return {
        init: init
    };
});