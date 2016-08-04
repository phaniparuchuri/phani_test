/**
 * Box file element index class
 */
Craft.BoxIndex = Craft.AssetIndex.extend(
{

	/**
	 * Initialize the uploader.
	 *
	 * @private
	 */
	onAfterHtmlInit: function()
	{
		// add upload button
		this.$uploadButton = $('<div class="btn submit" data-icon="upload" style="position: relative; overflow: hidden;" role="button">' + Craft.t('Upload files') + '</div>');
		this.addButton(this.$uploadButton);

		this.$uploadInput = $('<input type="file" multiple="multiple" name="box-upload" />').hide().insertBefore(this.$uploadButton);

		this.promptHandler = new Craft.PromptHandler();
		this.progressBar = new Craft.ProgressBar(this.$main, true);

		var options = {
			url: Craft.getActionUrl('box/uploadFile'),
			fileInput: this.$uploadInput,
			dropZone: this.$main
		};

		options.events = {
			fileuploadstart:       $.proxy(this, '_onUploadStart'),
			fileuploadprogressall: $.proxy(this, '_onUploadProgress'),
			fileuploaddone:        $.proxy(this, '_onUploadComplete')
		};

		if (typeof this.settings.criteria.kind != "undefined")
		{
			options.allowedKinds = this.settings.criteria.kind;
		}

		this._currentUploaderSettings = options;

		this.uploader = new Craft.Uploader (this.$uploadButton, options);

		this.$uploadButton.on('click', $.proxy(function()
		{
			if (this.$uploadButton.hasClass('disabled'))
			{
				return;
			}
			if (!this.isIndexBusy)
			{
				this.$uploadButton.parent().find('input[name=box-upload]').click();
			}
		}, this));
	}

});

// Register it!
Craft.registerElementIndexClass('Box_File', Craft.BoxIndex);