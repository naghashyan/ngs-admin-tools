let PageManager = {
  pageParams: {},
  initPageParams: function (pageParams) {
    this.pageParams = pageParams;
  },
  getGlobalParams: function () {
    let params = Object.assign({}, this.getPageParams());
    return params;
  },

  getPageParams: function () {
    return this.pageParams;
  }
};
export default PageManager;