var Strut = Strut || {};

Strut.Pagination = {
	Paginators: {},
	Create: function (opts, name) {
		var paginate = new Strut.Pagination.Paginator(opts);
		Strut.Pagination.Paginators[name] = paginate;
		return paginate;
	},
	Paginator: function (opts) {
		var defaults = {
			ItemsPerPage: 10,
			CurrentPage: 1,
			TotalPages: 1,
			TotalResults: undefined,
			VisiblePages: 5,
			PerPageOptions: [10, 25, 50, 100],
			DataSource: '',
			Target: undefined,
			Container: undefined,
			Header: undefined,
			SearchField: undefined,
			Export: undefined,
			PreFetchCallback: undefined,
			PostDrawCallback: undefined,
			//		PerPageSelector: undefined,
			Templates: {
				Wrapper: "paginator",
				LeftBtn: "page-left-btn",
				RightBtn: "page-right-btn",
				Btn: "page-btn",
				ManyMore: "page-more",
				NoResults: "no-pagination-results"
			},
			LoadOnInit: true
		}
		$.extend(true, this, defaults, opts);
		this.Init();
	},
	Impl: {
		Init: function () {
			// Add markup elements
			// Bind Buttons
			// Setup State
			// 

			if (!this.Container) {
				throw "Must provide a valid container when using the paginator";
			}
			if (!this.Target) {
				throw "Must provide a valid target when using the paginator";
			}

			this.Target.data('paginator', this);
			var wrapper = Strut.Common.Template.GetTemplateClone(this.Templates.Wrapper);
			this.Container.append(wrapper);

			var perPageSelector = this.Container.find('.results-per-page-holder select');
			//binding event
			perPageSelector.change(this._onPerPageChange.bind(this));
			perPageSelector.empty();

			for (var i = 0, len = this.PerPageOptions.length; i < len; i++) {
				var selected = (this.PerPageOptions[i] == this.ItemsPerPage) ? (" selected") : ("");
				perPageSelector.append("<option value=\"" + this.PerPageOptions[i] + "\"" + selected + ">" + this.PerPageOptions[i] + "</option>");
			}
			Strut.FormElements.Dropdowns(perPageSelector);
			this._onPerPageChange();

			//this.Container.off('click','[data-page]',_onPageClick.bind(this));
			this.Container.on('click', '[data-page]', this._onPageClick.bind(this));

			//this.Container.off('click','[data-page-left]',_onPagePrevClick.bind(this));
			this.Container.on('click', '[data-page-left]', this._onPagePrevClick.bind(this));

			//this.Container.off('click','[data-page-right]',_onPageNextClick.bind(this));
			this.Container.on('click', '[data-page-right]', this._onPageNextClick.bind(this));

			if(this.Header)
				this.Header.on('click','[data-paginate-order-by]', this._onOrderByClick.bind(this));

			if (this.SearchField) {
                this.SearchField.on('change', this._onSearch.bind(this));
                this.SearchField.next('[data-clear-search]').on('click', this.ClearSearch.bind(this));
            }
            
            if (this.Export) {
	            this.Export.on('click', this._exportCSV.bind(this));
            }

			if (this.LoadOnInit)
				this.Refresh();
			else
				this._Draw();
		},
		_onPageClick: function (event) {
			var ele = $(event.currentTarget);
			this.SetPage(ele.attr('data-page'));
		},
		_onPageNextClick: function (event) {
			this.NextPage();
		},
		_onPagePrevClick: function (event) {
			this.PrevPage();
		},
		_onPerPageChange: function (event) {
			var val = this.Container.find('.results-per-page-holder select').val();
			if (this.ItemsPerPage != val) {
				//find the offset of the top item on the current page
				var oldOffset = (this.CurrentPage - 1) * this.ItemsPerPage;
				//now find the page this offset lives on, using the new perpage, and set it
				this.CurrentPage = Math.floor(oldOffset / val) + 1;
				this.ItemsPerPage = val;

				this.Refresh();
			}
		},
		_exportCSV: function(event) {
			var ele = $(event.currentTarget);
			var ajaxData = this._compileData();
			ajaxData.limit = 0;
			ajaxData.offset = 0;
			Strut.Common.AjaxDownload(Strut.Common.BaseURL + ele.data('export'), ajaxData);
		},
		_onSearch: function (event) {
			this.Reset();
		},
		_onOrderByClick: function(event) {
			var currentSort = this.Header.find('[data-paginate-order-this]');
			if(currentSort.is(event.target)){
				//all we want to do is toggle the direction
				var currDir = currentSort.attr('data-paginate-order-dir');
				if(typeof currDir == "undefined")
					currDir = 'asc'; //set it to default if mia

				if(currDir == 'asc') //swap it
					currDir = 'desc';
				else
					currDir = 'asc';

				currentSort.attr('data-paginate-order-dir',currDir);

				//console.log('Toggle Dir', currDir);
			} else {
				currentSort.removeAttr('data-paginate-order-this data-paginate-order-dir');
				currentSort
				var newSort = $(event.target);
				newSort.attr('data-paginate-order-this',"");

				//console.log('Setting Sort:',newSort.attr('data-paginate-order-by'));
			}
			this.Reset();
		},
		ClearSearch: function() {
			this.SearchField.val('');
			this.Reset();
		},
		Reset: function () {
			this.CurrentPage = 1;
			this.Refresh();
		},
		Refresh: function () {
			this.FetchData();
		},
		FetchData: function () {
			// call for the data
			var ajaxData = this._compileData();
			//deal with the sorting capabilities
			if(this.Header){
				var orderCol = this.Header.find('[data-paginate-order-this]');
				if(orderCol.length > 0){
					var colName = orderCol.attr('data-paginate-order-by');
					if(colName)
						ajaxData.orderby = colName;
					var colDir = orderCol.attr('data-paginate-order-dir');
					if(colDir)
						ajaxData.orderdir = colDir;
				}
			}
			$.ajax({
				url: Strut.Common.BaseURL + this.DataSource,
				type: "POST",
				dataType: "json",
				data: ajaxData,
				success: this._onData.bind(this),
                error: function(data) {
                    console.log('Unable to fetch paginated content from data source or invalid data was returned.')
                }
			});

		},
		_compileData: function(){
			var ajaxData = {
				keyword: '',
				limit: this.ItemsPerPage,
				offset: (this.CurrentPage - 1) * this.ItemsPerPage
			};
			if (this.SearchField)
				ajaxData.keyword = this.SearchField.val();
			if (this.PreFetchCallback) {
				ajaxData = this.PreFetchCallback(ajaxData);
			}
			return ajaxData;
		},
		_onData: function (data) {
			//receive data
			this.TotalResults = data.total;
			this.TotalPages = Math.ceil(data.total / data.limit);
			this.TotalPages = Math.max(1, this.TotalPages);
			if (this.CurrentPage > this.TotalPages)
				this.CurrentPage = this.TotalPages; //keeps it never maxed out
			this._Draw(data.view);
		},
		NextPage: function () {
			this.SetPage(this.CurrentPage + 1);
		},
		PrevPage: function () {
			this.SetPage(this.CurrentPage - 1);
		},
		SetPage: function (pageNum) {
			if (pageNum > this.TotalPages)
				pageNum = this.TotalPages;
			if (pageNum < 1)
				pageNum = 1;
			this.CurrentPage = pageNum;
			this.Refresh();
		},
		_Draw: function (viewHtml) {
			if(typeof viewHtml != "undefined"){
				this.Target.html(viewHtml);
			}

			//now draw the paginator
			this.Container.show();
			var paginate = this.Container.find(".pagination");

			/*
			 Okay, so as per design, currently, we always put the bookends on.
			 - these should be eanbled based on if they can be used (first page vs last page..)
			 */

			paginate.find('li').not('[data-page-left], [data-page-right]').remove();
			//see if they are already there...
			var pageLeft = paginate.find('[data-page-left]');
			if (pageLeft.length == 0) {
				pageLeft = $('<li>');
				pageLeft.append(Strut.Common.Template.GetTemplateClone(this.Templates.LeftBtn));
				pageLeft.attr('data-page-left', '');
				paginate.prepend(pageLeft);
			}
			if (this.CurrentPage == 1)
				pageLeft.addClass('disabled');
			else
				pageLeft.removeClass('disabled');

			var pageRight = paginate.find('[data-page-right]');
			if (pageRight.length == 0) {
				pageRight = $('<li>');
				pageRight.append(Strut.Common.Template.GetTemplateClone(this.Templates.RightBtn));
				pageRight.attr('data-page-right', '');
				paginate.append(pageRight);
			}
			if (this.CurrentPage == this.TotalPages)
				pageRight.addClass('disabled');
			else
				pageRight.removeClass('disabled');

			/*
			 now we print the numbers, are use cases:
			 total pages <= visible pages  ---->  display all pages, no special bits
			 total pages > visible ----> display one or two "ManyMore"s
			 -> if current page > visiblePages/2, manymore at start
			 -> if current page < totalpages - visiblePages/2, many more at end

			 */

			var lastItem = pageLeft;
			var tmpEle, tmpBtn, i;

			var pageEdges = Math.ceil((this.VisiblePages - 1) / 2);

			var startPage = Math.max(this.CurrentPage - pageEdges, 2);
			var pageCount = startPage + this.VisiblePages - 1;

			//always page 1
			tmpEle = $('<li>');
			tmpBtn = Strut.Common.Template.GetTemplateClone(this.Templates.Btn);
			tmpBtn.text('1');
			tmpEle.attr('data-page', 1);
			tmpEle.append(tmpBtn);
			lastItem.after(tmpEle);
			lastItem = tmpEle;

			//first many more
			if (this.TotalPages > this.VisiblePages && this.CurrentPage > (pageEdges + 2)) {
				tmpEle = $('<li>');
				tmpEle.append(Strut.Common.Template.GetTemplateClone(this.Templates.ManyMore));
				tmpEle.addClass('disabled');
				lastItem.after(tmpEle);
				lastItem = tmpEle;
			}

			if (this.CurrentPage <= (pageEdges + 1))
				pageCount = this.VisiblePages;

			if (pageCount > this.TotalPages) {
				startPage -= (pageCount - this.TotalPages);
				pageCount = this.TotalPages;
			}

			if (startPage < 2)
				startPage = 2;

			//actual displayed pages
			for (i = startPage; i <= pageCount; i++) {
				tmpEle = $('<li>');
				tmpBtn = Strut.Common.Template.GetTemplateClone(this.Templates.Btn);
				tmpBtn.text(i);
				tmpEle.attr('data-page', i);
				tmpEle.append(tmpBtn);
				lastItem.after(tmpEle);
				lastItem = tmpEle;
			}

			//last many more
			if (this.TotalPages > this.VisiblePages && this.CurrentPage < this.TotalPages - pageEdges - 1) {
				tmpEle = $('<li>');
				tmpEle.append(Strut.Common.Template.GetTemplateClone(this.Templates.ManyMore));
				tmpEle.addClass('disabled');
				lastItem.after(tmpEle);
				lastItem = tmpEle;
			}

			//now we can look at the pageCount, and if it is less then total pages, we draw the last page
			if (pageCount < this.TotalPages) {
				tmpEle = $('<li>');
				tmpBtn = Strut.Common.Template.GetTemplateClone(this.Templates.Btn);
				tmpBtn.text(this.TotalPages);
				tmpEle.attr('data-page', this.TotalPages);
				tmpEle.append(tmpBtn);
				lastItem.after(tmpEle);
				lastItem = tmpEle;
			}

			//set active page
			paginate.find('[data-page="' + this.CurrentPage + '"]').addClass('active');
			
			//no results
			if(this.TotalResults == 0){
				var message = Strut.Common.Template.GetTemplateClone(this.Templates.NoResults);
				message.find('[data-no-results-colspan]').attr('colspan', this.Target.closest('table').find('th').length);
				this.Target.html(message);
				this.Container.hide();
			}

			if (this.PostDrawCallback)
				this.PostDrawCallback(this);

		}
	}
};

$.extend(Strut.Pagination.Paginator.prototype,Strut.Pagination.Impl);