import Dropzone from "../../../ngs/cms/lib/dropzone.min.js";
import DialogUtility from "./DialogUtility.js";


let ImageDropzoneUtil = {


    /**
     * need for collecting file names with error messages
     */
    totalErrorFiles: [],
    variables: {},
    dropzones: {},



    initVariables: function(variables) {
        for(let key in variables) {
            if(variables.hasOwnProperty(key)) {
                ImageDropzoneUtil.variables[key] = variables[key];
            }
        }
    },


    initDropzoneForViewMode: function (isDropzoneMultiple) {
        const _this = this;
        let dropzone = new Dropzone('.dropzone', {
            url: "/target-url", // Set the url

            init: function () {
                this.removeEventListeners();
                _this.showExistingImages(this, isDropzoneMultiple);
            }
        });
    },

    initSingleDropzoneForAddEditMode: function() {
        try {
            if ((document.querySelectorAll('#' + ImageDropzoneUtil.variables.container + ' .f_singleDropzone')).length === 0) {
                return;
            }
            const _this = this;
            new Dropzone('.f_singleDropzone', {

                url: "/target-url",
                parallelUploads: 50,
                autoProcessQueue: true,
                thumbnailHeight: 120,
                thumbnailWidth: 120,
                addRemoveLinks: true,
                dictDefaultMessage: "Upload Image",
                maxFilesize: 3,
                maxFiles: 1,
                filesizeBase: 1000,
                thumbnail: function (file, dataUrl) {
                    if (file.previewElement) {
                        file.previewElement.classList.remove("dz-file-preview");
                        let images = file.previewElement.querySelectorAll("[data-dz-thumbnail]");
                        for (let i = 0; i < images.length; i++) {
                            let thumbnailElement = images[i];
                            thumbnailElement.alt = file.name;
                            thumbnailElement.src = dataUrl;
                        }
                        setTimeout(function () {
                            file.previewElement.classList.add("dz-image-preview");
                        }, 1);
                    }
                },
                init: function () {
                    let index = 0;
                    _this.showExistingImages(this, false);

                    this.on("maxfilesexceeded", function (file) {
                        this.removeAllFiles();
                        this.addFile(file);
                    });
                    _this._initRemoveButtonForSingle();

                    //this is made by Lyov, need to discuss about when delete the browser alerts a question and many things
                    this.on("sending", function (file, xhr, data) {

                        if (_this.images.length) {
                            _this.images.splice(0, 1);
                        }
                        let removeButton = file.previewElement.querySelector('.dz-remove');
                        let attributeValue = String((++index) + (new Date()).getTime());
                        removeButton.setAttribute('data-unique-id', attributeValue);
                        let currentImage = {
                            [attributeValue]: file
                        };
                        _this.images.push(currentImage);


                        xhr.send = function () {
                            return true;
                        };
                        document.getElementsByClassName('dz-progress')[0].style.opacity = 0;
                        document.getElementsByClassName('dz-success-mark')[0].style.opacity = 1;
                        setTimeout(() => {
                            document.getElementsByClassName('dz-success-mark')[0].style.opacity = 0;
                        }, 500);
                    });

                        //this is made by by Aram , this is old version after discuss need to leave one of two versions
                    // this.on("complete", function (file) {
                    //
                    //     if (_this.images.length) {
                    //         _this.images.splice(0, 1);
                    //     }
                    //
                    //     let removeButton = file.previewElement.querySelector('.dz-remove');
                    //     let attributeValue = String((++index) + (new Date()).getTime());
                    //     removeButton.setAttribute('data-unique-id', attributeValue);
                    //     let currentImage = {
                    //         [attributeValue]: file
                    //     };
                    //     _this.images.push(currentImage);
                    // });

                }

            });
        } catch (e) {
            console.log(e);
        }
    },

    /**
     * dropzone initializing functionality for multiple images
     * param shouldShowExistingImages is true only at the first time of calling this function.
     * It determines that need to show existing images first, and under it init a new dropzone to upload
     * @param f_class
     * @param shouldShowExistingImages
     */
    initMultipleDropzoneForAddEditMode: function(f_class = 'f_1', shouldShowExistingImages = true) {

        try {
            if ((document.querySelectorAll('#' + ImageDropzoneUtil.variables.container + ' .f_multipleDropzone')).length === 0) {
                return;
            }

            document.querySelector('.upload-image-left').classList.add('multiple-upload');
            document.querySelector('.form-items-container').classList.remove('no-flex-wrap');

            const _this = this;

            this.dropzones[f_class] = new Dropzone('.' + f_class, {
                url: "/target-url", // Set the url
                parallelUploads: 100,
                thumbnailHeight: 120,
                thumbnailWidth: 120,
                addRemoveLinks: true,
                dictDefaultMessage: "Upload Multiple Images",
                maxFilesize: 3,
                filesizeBase: 1000,
                uploadMultiple: true,
                acceptedFiles: ".jpeg,.jpg,.png,.gif",
                thumbnail: function (file, dataUrl) {
                    if (file.previewElement) {
                        file.previewElement.classList.remove("dz-file-preview");
                        var images = file.previewElement.querySelectorAll("[data-dz-thumbnail]");
                        for (var i = 0; i < images.length; i++) {
                            var thumbnailElement = images[i];
                            thumbnailElement.alt = file.name;
                            thumbnailElement.src = dataUrl;
                        }
                        setTimeout(function () {
                            file.previewElement.classList.add("dz-image-preview");
                        }, 1);
                    }
                },
                init: function () {
                    if (shouldShowExistingImages) {
                        _this.showExistingImages(this, true, _this.dropzones[f_class]);
                    }

                    //seems it works fine only with this listener
                    // this.on("successmultiple", function() {

                    //need to choose the listener, which is better
                    this.on("queuecomplete", function() {

                        let files = this.files;
                        if(!files.length) {
                            return;
                        }

                        for (let i = 0; i < files.length; i++) {

                            //this is part is added from development branch, but its is under testing
                            if(files[i].status === 'error') {
                                continue;
                            }


                            let uniqueIdentifier = String((i) + (new Date()).getTime());
                            _this._setUniqueIdToRemoveButton(files[i], uniqueIdentifier);
                            _this.images.push({[uniqueIdentifier] : files[i]});
                            _this.addTitleAndRadioButtonToImageForNewAddedImage(uniqueIdentifier, f_class);
                        }

                        this.removeEventListeners();
                        _this._initNewDropzoneAppearingUnderLastOne(f_class);
                    });


                    this.on("error", function(file, errormessage) {
                        _this.totalErrorFiles.push({fileName: file.name, text: errormessage});
                        this.removeFile(file);

                        setTimeout(()=> {
                            if((_this.totalErrorFiles).length > 1) {
                                let errorTextOfEachError = '';
                                for(let i = 0; i < _this.totalErrorFiles.length; i++) {
                                    errorTextOfEachError += 'file <b>' + _this.totalErrorFiles[i].fileName + '</b> : ' + _this.totalErrorFiles[i].text + '<br />';
                                }

                                DialogUtility.showInfoDialog('Error', 'Was an error uploading these files. This is the error texts<br/>' + errorTextOfEachError, {actionResultShow: false, noButton: true, 'timeout' : _this.totalErrorFiles.length * 2500});

                            }else {
                                DialogUtility.showInfoDialog('Error', 'Was an error uploading "<b>' + _this.totalErrorFiles[0].fileName + '"</b> file. This is the error text <br />' + _this.totalErrorFiles[0].text, {actionResultShow: false, noButton: true, 'timeout' : 4000});
                            }

                        }, 10);

                        setTimeout(() => {
                            _this.totalErrorFiles = [];
                        }, 500);

                    });


                    _this._initRemoveButtonsForMultiple();

                }

            });
        } catch (e) {
            console.log(e);
        }
    },





    /**
     * remove button of new added image should have unique identifier, to give possibility to remove yet not saved image
     * @param file
     * @param uniqueId
     * @private
     */
    _setUniqueIdToRemoveButton: function(file, uniqueId) {
        let removeButton = file.previewElement.querySelector('.dz-remove');
        removeButton.setAttribute('data-unique-id', uniqueId);
    },


    /**
     * show existing images
     * in multiple dropzone initializing, this function should be called only once
     * @param _this
     * @param isMultiple
     * @param dropzone
     */
    showExistingImages: function(_this, isMultiple, dropzone = null) {
        let existingImagesPaths = ImageDropzoneUtil.variables['imagesUrls'];

        if (this._arrayIsNotEmpty(existingImagesPaths)) {

            for (let i = 0; i < existingImagesPaths.length; ++i) {
                let image = {'url': existingImagesPaths[i].url.original};

                _this.options.addedfile.call(_this, image);
                _this.options.thumbnail.call(_this, image, image.url);

                let hiddenInputWithProperties = this.createHiddenInputWithCurrentImageProperties(existingImagesPaths[i]);
                let currentImage = document.querySelector('img[src="' + existingImagesPaths[i].url.original + '"]');
                currentImage.closest('.dz-image')?.appendChild(hiddenInputWithProperties);


                let progress = document.querySelector('.dz-progress');
                let imageElement = progress.closest('.dz-preview');
                if (isMultiple) {
                    this.addTitleAndRadioButtonAndUrlBoxToImageForExistingImage(imageElement, i);
                } else {
                    this.removeDefaultImageIfHasNoWritePermission();
                }
                progress.remove();
            }

            if (ImageDropzoneUtil.variables.onlyOneDefaultImage) {
                let clickBtn = document.querySelector('.element-field .dz-remove');
                if (clickBtn) {
                    setTimeout(() => {
                        clickBtn.click();
                    }, 0);
                }else if(!ImageDropzoneUtil.variables.imagesHasWritePermission) {
                    let defaultImage = document.querySelector('.f_image-element-main-box');
                    if(defaultImage) {
                        defaultImage.remove();
                    }
                }
            }
        }

        if (isMultiple) {
            if(dropzone) {
                dropzone.removeEventListeners();
            }
            if(!ImageDropzoneUtil.variables.isViewMode) {
                this._initNewDropzoneAppearingUnderLastOne('f_1', false);
            }

        }

    },


    /**
     * under each image creates a hidden input with properties of image
     * @param image
     * @returns {HTMLInputElement}
     */
    createHiddenInputWithCurrentImageProperties: function(image) {
        let hiddenInputWithProperties = document.createElement('input');

        let id = image.url.original.substring(image.url.original.lastIndexOf('/') + 1);
        let thumbsInfo = {};

        if (!ImageDropzoneUtil.variables.onlyOneDefaultImage) {
            thumbsInfo.original = image.url.original;
        }
        if (image.url.small) {
            thumbsInfo.small = image.url.small;
        }
        if (image.url.medium) {
            thumbsInfo.medium = image.url.medium;
        }
        if (image.url.big) {
            thumbsInfo.big = image.url.big;
        }
        hiddenInputWithProperties.setAttribute('type', 'hidden');
        hiddenInputWithProperties.setAttribute('value', id);
        hiddenInputWithProperties.setAttribute('thumbsInfo', JSON.stringify(thumbsInfo));
        hiddenInputWithProperties.classList.add('f_hidden-input-image-id');

        return hiddenInputWithProperties;
    },


    /**
     * initializing remove buttons correct working for both, new added and old existing images
     * @private
     */
    _initRemoveButtonsForMultiple: function() {
        let removeButtons = document.querySelectorAll('.dz-remove');
        let imageMainBoxes = [];

        removeButtons.forEach((button, buttonIndex) => {
            imageMainBoxes[buttonIndex] = removeButtons[buttonIndex].closest('.f_image-element-main-box');
            button.addEventListener('click', (evt) => {

                let hiddenInputWithCurrentImageProperties = evt.target.closest('.dz-image-preview')?.querySelector('.f_hidden-input-image-id');
                if (hiddenInputWithCurrentImageProperties) {
                    let clickedImageId = hiddenInputWithCurrentImageProperties.value;
                    let indexOfCurrentImageIdInImagesArray = this.existingImagesIds.findIndex((imageId) => imageId === clickedImageId);
                    this.existingImagesIds[indexOfCurrentImageIdInImagesArray] = null;
                }

                imageMainBoxes[buttonIndex].remove();

                //this is the case, when new added image (yet not saved) is removed, need just to remove it from array of new added images
                let imgKey = evt.target.getAttribute('data-unique-id');

                this.images.forEach((image) => {
                    if (imgKey in image) {
                        let i = this.images.indexOf(image);
                        this.images.splice(i, 1);
                    }
                });

                if (!document.querySelectorAll('.element-field')[0].classList.contains('dz-started')) {
                    document.querySelectorAll('.element-field')[0].classList.add('dz-started');
                }

                if (!document.querySelector('.f_image-element-main-box')) {

                    let allDropzones = document.querySelectorAll('.f_multipleDropzone');
                    allDropzones.forEach((elem) => {
                        elem.remove();
                    });
                    let newDropzone = this._createNewDropzone('f_0');

                    document.querySelector('.f_all-dropzones-container').appendChild(newDropzone);
                    this.initMultipleDropzoneForAddEditMode('f_1', false);
                }
            });
        })
    },



    _initRemoveButtonForSingle: function() {
        let removeButton = document.querySelector('.dz-remove');
        if (removeButton) {
            removeButton.addEventListener('click', () => {
                this.existingImagesIds[0] = null;
            })
        }


    },





    _arrayIsNotEmpty: function(existingImagesPaths) {
        if (existingImagesPaths === undefined || existingImagesPaths === [undefined] || existingImagesPaths[0] === undefined || existingImagesPaths[0] === [undefined]) {
            return false;
        }
        if (existingImagesPaths.every((i) => i === undefined || i === null)) {
            return false;
        }
        return true;
    },


    /**
     * each image that is new added should have image title box, and radio button
     * @param identifier
     * @param f_class
     */
    addTitleAndRadioButtonToImageForNewAddedImage: function(identifier, f_class) {
        let titleAndRadioButtonContainer = this.createTitleBox(identifier);
        let imageElementMainBox = document.createElement('div');
        imageElementMainBox.classList.add('image-upload-multiple-box', 'f_image-element-main-box', 'f_newAddedImage');

        let dzPreview = document.querySelector('.' + f_class).querySelector('.dz-preview.dz-success');
        let currentDropzone = document.querySelector('.' + f_class);

        imageElementMainBox.appendChild(dzPreview);
        imageElementMainBox.appendChild(titleAndRadioButtonContainer);
        currentDropzone.appendChild(imageElementMainBox);
    },


    /**
     * each image in edit mode should have image title box with url of image, copy button, and radio button
     * @param imageElement
     * @param indexOfImage
     */
    addTitleAndRadioButtonAndUrlBoxToImageForExistingImage: function(imageElement, indexOfImage) {
        let titleBox = this.createTitleBox(null, imageElement, indexOfImage);

        let containerForImageAndUrlBoxAndButtons = document.createElement('div');
        containerForImageAndUrlBoxAndButtons.classList.add('image-upload-multiple-box', 'f_image-element-main-box', 'f_oldImage');

        let firstDropzone = imageElement.closest('.f_1.f_multipleDropzone');
        containerForImageAndUrlBoxAndButtons.appendChild(imageElement);
        containerForImageAndUrlBoxAndButtons.appendChild(titleBox);
        firstDropzone.appendChild(containerForImageAndUrlBoxAndButtons);
    },


    /**
     * there are 3 main elements that this function creates: title box in its container, radio button in ints container, and copy button (with link) in its container
     * if is passed identifier only as argument, so its for new added image, and should not have url box and copy button box.
     * if are passed imageElement and imageIndex, so its for existing images, and should have url box and copy button box.
     *
     * @param identifier
     * @param imageElement
     * @param imageIndex
     * @returns {HTMLDivElement}
     */
    createTitleBox: function(identifier, imageElement = null, imageIndex = null) {
        let containerOfTitleAndButtons = document.createElement('div');
        containerOfTitleAndButtons.classList.add('info-box');
        let radioButtonContainer = document.createElement('div');
        radioButtonContainer.classList.add('col-4');
        let titleContainer = document.createElement('div');
        titleContainer.classList.add('form-item', 'full-box');
        let titleBox = document.createElement('div');
        titleBox.classList.add('input-field');

        radioButtonContainer.appendChild(this.createIsMainRadioButton(imageIndex));
        titleContainer.appendChild(titleBox);

        let labelTagForTitle = document.createElement('label');
        labelTagForTitle.classList.add('active', 'qweqwe');

        labelTagForTitle.innerText = "Image Title";
        titleBox.appendChild(labelTagForTitle);

        let inputTagForTitle = document.createElement('input');
        inputTagForTitle.setAttribute('name', 'imageDescription');

        titleBox.appendChild(inputTagForTitle);
        containerOfTitleAndButtons.appendChild(titleContainer);
        containerOfTitleAndButtons.appendChild(radioButtonContainer);

        if (!identifier && imageElement) {  //for existing images
            containerOfTitleAndButtons.appendChild(this.createUrlOfImageAndCopyButton(inputTagForTitle, imageIndex));
            let imageId = imageElement.querySelector('.f_hidden-input-image-id').value;
            inputTagForTitle.setAttribute('image-id', 'old_' + imageId);
        }else {                             //for new added images
            let imageId = 'new_' + identifier;
            inputTagForTitle.setAttribute('image-id', imageId);
        }

        return containerOfTitleAndButtons;
    },


    /**
     * return container of url of image with its label ("Link") and button for copy image url
     * @param inputTagForTitle
     * @param imageIndex
     * @returns {HTMLDivElement}
     */
    createUrlOfImageAndCopyButton(inputTagForTitle, imageIndex) {
        let existingImagesPaths = ImageDropzoneUtil.variables['imagesUrls'];

        let urlAndCopyButtonContainer = document.createElement('div');
        urlAndCopyButtonContainer.classList.add('url-box', 'col-8');


        let urlTextItem = document.createElement('div');
        urlTextItem.classList.add('url-text-item');

        let urlText = document.createElement('span');
        urlText.classList.add('url-text', 'medium1');
        urlText.innerHTML = `<a class="url-text-inner" href="${existingImagesPaths[imageIndex].url.original}" target="_blank">${existingImagesPaths[imageIndex].url.original}<a/>`;

        inputTagForTitle.value = existingImagesPaths[imageIndex].description;

        let labelForUrlLabel = document.createElement('div');
        labelForUrlLabel.classList.add('url-label', 't5');
        labelForUrlLabel.innerText = 'Link';

        urlTextItem.appendChild(labelForUrlLabel);
        urlTextItem.appendChild(urlText);

        urlAndCopyButtonContainer.appendChild(urlTextItem);
        return urlAndCopyButtonContainer
    },


    //todo: check this
    createIsMainRadioButton: function(index) {
        let isMainRadioButtonContainer = document.createElement('div');
        isMainRadioButtonContainer.classList.add('radiobox-item');
        let labelForIsMainRadioTextBox = document.createElement('div');
        labelForIsMainRadioTextBox.classList.add('text-item-box', 'medium1');
        labelForIsMainRadioTextBox.innerText = 'Main Image';

        if(ImageDropzoneUtil.variables.isViewMode || !ImageDropzoneUtil.variables.imagesHasWritePermission) {
            if(index !== null) {
                if(ImageDropzoneUtil.variables['imagesUrls'][index].isMain) {
                    isMainRadioButtonContainer.classList.add('checkbox-item');
                    let isMainRadioButtonLabel = document.createElement('label');
                    let isMainRadioButtonSpan = document.createElement('span');
                    isMainRadioButtonSpan.classList.add('view-checkbox', 'checked');
                    let isMainRadioButtonIcon = document.createElement('i');
                    isMainRadioButtonIcon.classList.add('icon-svg257', 'not-checked');

                    isMainRadioButtonSpan.appendChild(isMainRadioButtonIcon);
                    isMainRadioButtonLabel.appendChild(isMainRadioButtonSpan);
                    isMainRadioButtonLabel.appendChild(labelForIsMainRadioTextBox);
                    isMainRadioButtonContainer.appendChild(isMainRadioButtonLabel);
                }
            }
        }else {

            let isMainRadioButton = document.createElement('input');
            isMainRadioButton.classList.add('f_isMainRadioButton');
            isMainRadioButton.setAttribute('type', 'radio');
            isMainRadioButton.setAttribute('name', 'isMainImage');
            let labelForIsMainRadioCheckBox = document.createElement('span');

            let labelForIsMainRadio = document.createElement('label');
            labelForIsMainRadio.appendChild(isMainRadioButton);
            labelForIsMainRadio.appendChild(labelForIsMainRadioCheckBox);
            labelForIsMainRadio.appendChild(labelForIsMainRadioTextBox);

            let labelForIsMainRadioBoxLabel = document.createElement('div');
            labelForIsMainRadioBoxLabel.classList.add('radio-label', 't5');
            labelForIsMainRadioBoxLabel.innerText = 'Use as';

            isMainRadioButtonContainer.appendChild(labelForIsMainRadioBoxLabel);
            isMainRadioButtonContainer.appendChild(labelForIsMainRadio);

            if(index !== null) {
                if(ImageDropzoneUtil.variables['imagesUrls'][index].isMain) {
                    isMainRadioButton.checked = true;
                }
            }
        }

        return isMainRadioButtonContainer;

    },


    removeDefaultImageIfHasNoWritePermission: function() {
        if(ImageDropzoneUtil.variables.onlyOneDefaultImage && !ImageDropzoneUtil.variables.imagesHasWritePermission) {
            let imageContainer = document.querySelector('.f_all-dropzones-container');
            if(imageContainer) {
                let containerParent = imageContainer.closest('.form-item');
                if(containerParent) {
                    containerParent.remove();
                }else {
                    imageContainer.remove();
                }
            }
        }
    },





    /**
     * there are 2 times, that need to init new Dropzone under the main one:
     * 1: the first time, after showing existing images
     * 2: after each time some image is uploaded
     * @param f_class
     * @private
     */
    _initNewDropzoneAppearingUnderLastOne: function(f_class) {
        let allDropzonesContainer = document.querySelector(".f_all-dropzones-container");

        if(!allDropzonesContainer.querySelector('.f_image-element-main-box')) {
            return;
        }

        let newDropzone = this._createNewDropzone(f_class);
        let newClass = this._createNewClass(f_class);
        let allImages = document.querySelectorAll('.f_image-element-main-box');
        let allDropzoneElements = allDropzonesContainer.querySelectorAll('.f_multipleDropzone');
        let theFirstDropzone = allDropzoneElements[0];

        allImages.forEach((image) => {
            theFirstDropzone.appendChild(image);
        });

        for (let i = 1; i < allDropzoneElements.length; i++) {
            allDropzoneElements[i].remove();
        }

        allDropzonesContainer.appendChild(newDropzone);
        this.initMultipleDropzoneForAddEditMode(newClass, false);
    },



    /**
     * new dropzone element which is just a div, and need to be initialized yet
     * @param f_class
     * @returns {HTMLDivElement}
     * @private
     */
    _createNewDropzone: function(f_class) {
        let newDropzone = document.createElement('div');
        let newClass = this._createNewClass(f_class);
        newDropzone.classList.add('element-field', 'image-select-box', 'dropzone', newClass, 'f_multipleDropzone');
        return newDropzone;
    },


    /**
     * takes f_n as argument and returns f_n+1
     * @param f_class
     * @returns {string}
     * @private
     */
    _createNewClass: function(f_class) {
        let index = f_class.indexOf('_');
        let increment = +f_class.substr(index + 1) + 1;
        return 'f_' + increment;
    },


    /**
     * if images have description boxes (only in products for now), in view mode that descriptions should be not editable, that is should be spans
     * @private
     */
    _changeInputsIntoSpanInViewModeOnly: function() {
        let allDescriptionInputsFormItems = document.querySelectorAll('.f_multipleDropzone .f_image-element-main-box .info-box .form-item');
        let allValuesOfInputs = [...allDescriptionInputsFormItems].map((item) => item.querySelector('.input-field input[name="imageDescription"]').value);
        allValuesOfInputs.forEach((input, i) => {
            allDescriptionInputsFormItems[i].classList.add('view-mode');
            let inputField = allDescriptionInputsFormItems[i].querySelector('.input-field');
            allDescriptionInputsFormItems[i].querySelector('input[name="imageDescription"]').remove();
            let span = document.createElement('span');
            span.classList.add('view-text', 'f_image-description');
            span.innerText = input;
            inputField.appendChild(span);
        });
    }


};

export default ImageDropzoneUtil;





