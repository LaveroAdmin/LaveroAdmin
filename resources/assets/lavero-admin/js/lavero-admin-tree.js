/*-------------------------------------------------*/
/* tree */
/*-------------------------------------------------*/

admin.tree = {

    elm : false,
    url : false,
    sortable : false,
    settings : {},

    sortableDefaults : {
        group: 'nested',
        animation: 150,
        fallbackOnBody: false,
        swapThreshold: 0.65,
        emptyInsertThreshold: 15,
        handle: '.dd-handle',
        filter: '.dd-nodrag',
        preventOnFilter: false
    },

    init : function(elm, settings, url){
        this.url = url;
        this.elm = elm;
        this.settings = typeof settings === 'string' ? JSON.parse(settings || '{}') : (settings || {});

        let container = document.querySelector("#" + elm);
        if (!container) return;

        this.initSortables();
        this.updateItems();

        if (!container._treeBound) {
            container._treeBound = true;
            container.addEventListener("click", function(e) {
                let btn = e.target.closest("button[data-action]");
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();

                let action = btn.getAttribute("data-action");
                let item = btn.closest(".dd-item");
                if (!item) return;

                if (action === "collapse") {
                    item.classList.add("dd-collapsed");
                } else if (action === "expand") {
                    item.classList.remove("dd-collapsed");
                }
            });
        }
    },

    initSortables : function() {
        let nestedSortables = document.querySelectorAll("#" + this.elm + " ol");
        let sortableSettings = merge_default(this.sortableDefaults, this.settings || {});

        let self = this;
        sortableSettings.onStart = function(evt) {
            let container = document.querySelector("#" + self.elm);
            if (container) container.classList.add("dd-dragging");
        };

        sortableSettings.onEnd = function(evt) {
            let container = document.querySelector("#" + self.elm);
            if (container) container.classList.remove("dd-dragging");
            self.updateItems();
            self.initSortables();
        };

        sortableSettings.onMove = function(evt) {
            // Prevent dragging a parent into its own children/descendants
            if (evt.dragged && evt.to && evt.dragged.contains(evt.to)) {
                return false;
            }
        };

        for (var i = 0; i < nestedSortables.length; i++) {
            if (!nestedSortables[i]._sortable) {
                nestedSortables[i]._sortable = new Sortable(nestedSortables[i], sortableSettings);
                if (!this.sortable) {
                    this.sortable = nestedSortables[i]._sortable;
                }
            }
        }
    },

    updateItems : function() {
        let container = document.querySelector("#" + this.elm);
        if (!container) return;

        container.querySelectorAll(".dd-item").forEach(item => {
            let ol = item.querySelector(":scope > ol");
            let hasChildren = ol && ol.querySelector(":scope > li");

            if (hasChildren) {
                item.classList.add("has-children");
            } else {
                item.classList.remove("has-children");
                item.classList.remove("dd-collapsed");
            }
        });
    },

    delete : function(id){
        let resource_url = this.url + "/" + id;
        admin.resource.delete_do(resource_url);
    },

    save : function(){
        let order = this.toArrayNested();
        admin.ajax.loadPost(this.url, {_order: JSON.stringify(order)});
    },

    toArrayNested : function(){
        let top = document.querySelector("#" + this.elm + " > ol");
        return this.getChildren(top);
    },

    getChildren : function(elm){
        let arr = [];
        elm.querySelectorAll(":scope > li").forEach(li => {
            let obj = { id: li.dataset.id };
            let ol = li.querySelector(":scope > ol");
            if (ol && ol.querySelector(":scope > li")) {
                obj.children = this.getChildren(ol);
            }
            arr.push(obj);
        });
        return arr;
    },

    collapse : function(){
        document.querySelectorAll("#" + this.elm + " .dd-item.has-children").forEach(item => {
            item.classList.add("dd-collapsed");
        });
    },

    expand : function(){
        document.querySelectorAll("#" + this.elm + " .dd-item.has-children").forEach(item => {
            item.classList.remove("dd-collapsed");
        });
    }
};
