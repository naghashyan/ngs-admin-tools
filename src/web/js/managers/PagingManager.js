/**
 * @author Levon Naghashyan
 * @site http://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2012-2019
 */
let PagingManager = {
    init: function (callBack, container) {
        let pagingBox = null;
        if(container) {
            pagingBox = container.querySelectorAll(".f_pageingBox")[0];
        }
        else {
            pagingBox = document.querySelectorAll(".f_pageingBox")[0];
        }
        if(!pagingBox){
            return;
        }

        let currentPage = parseInt(pagingBox.getAttribute("data-im-page"));
        let pageCount = parseInt(pagingBox.getAttribute("data-im-page-count"));
        let currentLimit = parseInt(pagingBox.getAttribute("data-im-limit"));
        pagingBox.querySelectorAll('.f_page').forEach(function (page) {
            page.addEventListener('click', function (evt) {
                let selectedPage = parseInt(pagingBox.getAttribute("data-im-page"));
                goTo(parseInt(evt.currentTarget.getAttribute("data-im-page")));
            });
        });
        pagingBox.querySelectorAll(".f_go_to_page")[0].addEventListener('keyup', function (evt) {
            if (evt.keyCode === 13) {
                goTo(parseInt(evt.currentTarget.value));
                evt.preventDefault();
            }
        });

        pagingBox.querySelectorAll(".f_go_to_page")[0].addEventListener('keypress', function (evt) {
             if (evt.keyCode === 13) {
                goTo(parseInt(evt.currentTarget.value));
                evt.preventDefault();
            }
        });


        pagingBox.querySelectorAll(".f_count_per_page")[0].addEventListener('change', function (evt) {
            goTo(1, parseInt(evt.currentTarget.value));
        });
        let goTo = function (page, limit) {
            if(PagingManager.isLoading) {
                return;
            }
            if(limit && limit !== currentLimit){
                callBack({
                    page: page,
                    limit: limit
                });
                return;
            }
            if(page !== currentPage && page <= pageCount){
                callBack({
                    page: page,
                    limit: currentLimit
                });
            }
        };
    },
    getParams: function () {

        //todo: this part should be checked. Before it was id selector, but both will not work correctly
        let pagingBox = document.querySelectorAll(".f_pageingBox")[0];
        if(!pagingBox){
            return {};
        }
        let currentPage = parseInt(pagingBox.getAttribute("data-im-page"));
        let pageCount = parseInt(pagingBox.getAttribute("data-im-page-count"));
        let currentLimit = parseInt(pagingBox.getAttribute("data-im-limit"));
        return {
            page: currentPage,
            limit: currentLimit,
            pageCount: pageCount
        };
    }
};
export default PagingManager;
