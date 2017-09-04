"use strict";

(function ($) {
    $( document ).ready( function () {
        var user = new UserEdit();
        user.init($(".js-UserEdit"));
    });
})(jQuery);

function UserEdit() {

    this.container = null;
    this.eventElement = null;
    this.event = null;
    this.userRow = null;
    /*
    Initialize event handlers
     */
    this.init = function (container) {
        console.log("UserEdit constructor");
        this.container = container;

        this.setEditAction();
        this.setDeleteConfirmAction();
    };

    this.setEditAction = function () {
        var self = this;
        this.container.find(".js-user-edit").on("click", function (e) {
            self.eventElement = this;
            self.event = e;
            self.getPrimaryData();
            self.editAction();
        });
    };

    this.setDeleteConfirmAction = function () {
        var self = this;
        this.container.find(".js-user-delete").on("click", function (e) {
            self.eventElement = this;
            self.event = e;
            self.getPrimaryData();
            self.deleteConfirmAction();
        });
    };

    this.setDeleteAction = function () {
        var self = this;

        $('#' + this.getModalId("delete")).find(".js-delete").on("click", function (e) {
            self.eventElement = $(this).closest(".modal");
            self.event = e;
            self.deleteAction();
        });
    };

    this.setSaveAction = function() {
        var self = this;

        $('#' + this.getModalId("edit")).find(".js-user-save").on("click", function (e) {
            self.eventElement = $(this).closest(".modal");
            self.event = e;
            self.saveAction();
        });
    };

    this.getPrimaryData = function () {
        this.userRow = $(this.eventElement).closest(".js-user");
        this.id = this.userRow.attr("data-id");
        this.email = this.userRow.find(".js-email").text();
    };

    this.getModalId = function (code) {
        return "modal_" + code + "_user_" + this.id;
    };

    this.editAction = function () {
        var self = this;
        var code = "edit";

        var modal = $('#' + this.getModalId(code));

        if(modal.length <= 0){
            $.ajax({
                type: 'POST',
                url: Routing.generate('xhr-admin-user-edit'),
                data: {
                    id: this.id
                },
                success: function (data) {
                        var tplText = $('#js-template-edit').html();
                        var tpl = _.template(tplText);
                        var modalCode = tpl({
                            modalId: self.getModalId(code),
                            id: data.id,
                            email: self.email,
                            error: data.error,
                            errorMsg: data.errorMsg,
                            roles: data.roles
                        });
                        $("body").append(modalCode);
                        self.setSaveAction();
                        $('#' + self.getModalId(code)).modal('show');
                },
                dataType: "json"
            });
        }else{
            modal.modal('show');
        }
    };

    this.deleteConfirmAction = function () {
        var self = this;

        var code = "delete";

        var tplText = $('#js-template-delete_confirm').html();
        var tpl = _.template(tplText);
        var modalCode = tpl({
            modalId: self.getModalId(code)
        });
        $("body").append(modalCode);
        $('#' + self.getModalId(code)).modal('show');

        this.setDeleteAction();
    };

    this.deleteAction = function () {
        var self = this;

        $.ajax({
            type: 'POST',
            url: Routing.generate('xhr-admin-user-delete'),
            data: {
                id: this.id
            },
            success: function (data) {
                console.log(data.errorMsg);
                self.userRow.remove();
                self.eventElement.modal('hide');
            },
            dataType: "json"
        });
    };

    this.saveAction = function () {
        var self = this;

        $.ajax({
            type: 'POST',
            url: Routing.generate('xhr-admin-user-save'),
            data: this.eventElement.find('form').serialize(),
            success: function (data) {
                if(data.error){
                    self.eventElement.find(".js-error").html("<div class='alert alert-danger'>" + data.errorMsg + "</div>");
                    window.setTimeout(function() {
                        $(".alert").fadeTo(500, 0).slideUp(500, function(){
                            $(this).remove();
                        });
                    }, 4000);
                    return null;
                }

                self.updateRoles(data.roles)

                self.eventElement.modal('hide');
            },
            dataType: "json"
        });
    };

    this.updateRoles = function (roles) {
        this.userRow.find(".js-roles").empty();
        var tplText = $('#js-role-row').html();
        for(var key in roles){
            var tpl = _.template(tplText);
            var roleCode = tpl({
                role: roles[key]
            });
            this.userRow.find(".js-roles").append(roleCode);
        }
    };
}