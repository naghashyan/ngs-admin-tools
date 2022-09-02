import Dropzone from "../../../ngs/AdminTools/lib/dropzone.min.js";
import DialogUtility from "./DialogUtility.js";


export default class ImageDropzoneUtil {

    constructor() {
        this.totalErrorFiles = [];
        this.variables = {};
        this.dropzones = {};
        this.existingImagesIds = [];
        this.images = [];
        this.MAX_UPLOAD_IMAGE_SIZE_IN_BYTES = 3145728;
        this.initDefaultListeners();
    }


    initDefaultListeners() {
        let mageUploadContainer= document.querySelector('.f_upload-image-from-pc');

        if(!mageUploadContainer){
            return;
        }

        mageUploadContainer.addEventListener('click', () => {
            if (!this.lastDropzone) {
                return;
            }
            this.dropzones[this.lastDropzone].hiddenFileInput.click();
        });

        document.querySelector('main').addEventListener('click', (event) => {
            let multipleUploadCotainer = document.querySelector('.f_select-upload-tag');

            if (!multipleUploadCotainer) {
                return;
            }

            if (event.target.closest('.dz-default') === null && !multipleUploadCotainer.classList.contains('is_hidden')) {
                multipleUploadCotainer.classList.add('is_hidden');
            }
        });

    }

    /**
     * Init income  variables   for speicify
     *
     *  imagesUrls;
     *  isViewMode;
     *  imagesHasWritePermission;
     *  container;
     *  onlyOneDefaultImage;
     */
    initVariables(variables) {
        for (let key in variables) {
            if (variables.hasOwnProperty(key)) {
                this.variables[key] = variables[key];
            }
        }
    };


    initDropzoneForViewMode(isDropzoneMultiple) {
        const dropzoneUtilContext = this;
        let dropzone = new Dropzone('.dropzone', {
            url: "/target-url", // Set the url

            init: function () {
                this.removeEventListeners();
                dropzoneUtilContext.showExistingImages(this, isDropzoneMultiple);
            }
        });
    };

    initSingleDropzoneForAddEditMode() {
        try {
            if ((document.querySelectorAll('#' + this.variables.container + ' .f_singleDropzone')).length === 0) {
                return;
            }
            const _this = this;

            let dropzoneOptions = _this.getDropzoneOptions(false);
            new Dropzone('.f_singleDropzone', Object.assign(dropzoneOptions, {
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

                }
            }));
        } catch (e) {
            console.log(e);
        }
    };

    /**
     * dropzone initializing functionality for multiple images
     * param shouldShowExistingImages is true only at the first time of calling this function.
     * It determines that need to show existing images first, and under it init a new dropzone to upload
     * @param f_class
     * @param shouldShowExistingImages
     */
    initMultipleDropzoneForAddEditMode(f_class = 'f_1', shouldShowExistingImages = true) {
        try {
            if ((document.querySelectorAll('#' + this.variables.container + ' .f_multipleDropzone')).length === 0) {
                return;
            }
            let _this = this;
            const dropzoneUtilContext = this;
            _this.initMulipleDropzoneStyles();

            let uploadFromPcClass = `f_upload-image-from-pc`;

            let uploadFromPcBtn = document.querySelector('.' + f_class).closest(".f_all-dropzones-container").querySelector("." + uploadFromPcClass);

            let dropzonesOptions = _this.getDropzoneOptions(true, uploadFromPcBtn, f_class);

            _this.lastDropzone = f_class;

            _this.dropzones[f_class] = new Dropzone('.' + f_class, Object.assign(dropzonesOptions, {
                init: function () {
                    if (shouldShowExistingImages) {
                        _this.showExistingImages(this, true, _this.dropzones[f_class]);
                    } else {
                        _this.initUploadMultipleClick(f_class);
                    }

                    if (shouldShowExistingImages && !_this.variables['imagesUrls']) {
                        _this.initUploadMultipleClick(f_class);
                    }

                    this.on("queuecomplete", function () {
                        let files = this.files;

                        files.forEach((item, index) => {
                            //this is part is added from development branch, but its is under testing
                            if (item.status === 'error') {
                                return;
                            }

                            let uniqueIdentifier = String((index) + (new Date()).getTime());
                            _this._setUniqueIdToRemoveButton(item, uniqueIdentifier);
                            _this.images.push({[uniqueIdentifier]: item});
                            _this.addTitleAndRadioButtonToImageForNewAddedImage(uniqueIdentifier, f_class);
                        });

                        _this._initNewDropzoneAppearingUnderLastOne(f_class);
                        this.removeEventListeners();
                    });

                    this.on("error", function (file, errormessage) {
                        dropzoneUtilContext.totalErrorFiles.push({fileName: file.name, text: errormessage});
                        this.removeFile(file);

                        setTimeout(() => {
                            if ((dropzoneUtilContext.totalErrorFiles).length > 1) {
                                let errorTextOfEachError = '';
                                for (let i = 0; i < dropzoneUtilContext.totalErrorFiles.length; i++) {
                                    errorTextOfEachError += 'file <b>' + dropzoneUtilContext.totalErrorFiles[i].fileName + '</b> : ' + dropzoneUtilContext.totalErrorFiles[i].text + '<br />';
                                }

                                DialogUtility.showInfoDialog('Error', 'Was an error uploading these files. This is the error texts<br/>' + errorTextOfEachError, {
                                    actionResultShow: false,
                                    noButton: true,
                                    'timeout': dropzoneUtilContext.totalErrorFiles.length * 2500
                                });

                            } else {
                                DialogUtility.showInfoDialog('Error', 'Was an error uploading "<b>' + dropzoneUtilContext.totalErrorFiles[0].fileName + '"</b> file. This is the error text <br />' + dropzoneUtilContext.totalErrorFiles[0].text, {
                                    actionResultShow: false, noButton: true, 'timeout': 4000
                                });
                            }

                        }, 10);

                        setTimeout(() => {
                            dropzoneUtilContext.totalErrorFiles = [];
                        }, 500);

                    });

                    _this._initRemoveButtonsForMultiple();
                }
            }));

        } catch (e) {
            console.log(e);
        }
    };


    initUploadMultipleClick(fClass) {
        let emptyFileDropzone = document.querySelectorAll('.f_multipleDropzone');
        let dropzoneUtilContext = this;

        if (!emptyFileDropzone.length) {
            return;
        }

        let currentEptyElement = emptyFileDropzone.length === 1 ? emptyFileDropzone[0] : emptyFileDropzone[1];
        let uploadTagsConatiner = currentEptyElement.parentNode.querySelector('.f_select-upload-tag');

        currentEptyElement.addEventListener('click', (event) => {
            uploadTagsConatiner.classList.toggle('is_hidden');
        });


        document.querySelector('.f_upload-image-from-pc').addEventListener('click', () => {
            if (uploadTagsConatiner) {
                uploadTagsConatiner.classList.add('is_hidden');
            }
        });

        document.querySelector('.f_upload-image-from-link').addEventListener('click', () => {
            if (uploadTagsConatiner) {
                uploadTagsConatiner.classList.add('is_hidden');
            }

            DialogUtility.showCustomDialog("Upload file from URL", dropzoneUtilContext.getUploadFromUrlhtml(), {
                cancelBtnText: "Cancel",
                okBtnText: "Upload",
                shouldReverseButton: true,
                okBtnCustomClass: "primary",
                cancelBtnCustomClass: "",
                checkValidity: true,
                dialogLayoutClass: "custom-dialog-toast"
            }).then(dataUrl => {
                let url = dataUrl;

                fetch(url, {credentials: 'same-origin', mode: 'cors'})
                    .then((response) => response.blob())
                    .then((blob) => {
                        console.log(blob);
                        let fileExtension = dropzoneUtilContext.getFileNameFromBlobType(blob.type);
                        let file = new File([blob], `newImage.${fileExtension}`, {type: blob.type});

                        if (!dropzoneUtilContext.isUploadFileValid(file, fileExtension)) {
                            DialogUtility.showErrorDialog('Error', 'Was an error uploading "<b>' + url + '"</b> link file. This is not valid file for upload <br />', {
                                actionResultShow: true, 'timeout': 4000
                            });
                            return;
                        }

                        this.dropzones[fClass].emit("addedfile", file);
                        this.dropzones[fClass].emit("thumbnail", file, url);
                        this.dropzones[fClass].emit("success", file);
                        this.dropzones[fClass].emit("complete", file);
                        this.dropzones[fClass].files.push(file);
                    })
                    .catch(error => {
                        DialogUtility.showErrorDialog('Error', 'Was an error uploading on Request link file. This request is not valid<br />' + error, {
                            actionResultShow: true, 'timeout': 4000
                        });
                    })
            }).catch(error => {
                DialogUtility.showErrorDialog('Error', 'Was an error on uploading.  <br />' + error, {
                    actionResultShow: true, 'timeout': 4000
                });
            })

        });

    }

    /**
     * each image in edit mode should have image title box with url of image, copy button, and radio button
     * @param imageElement
     * @param indexOfImage
     */
    addTitleAndRadioButtonAndUrlBoxToImageForExistingImage(imageElement, indexOfImage) {
        let titleBox = this.createTitleBox(null, imageElement, indexOfImage);

        let containerForImageAndUrlBoxAndButtons = document.createElement('div');
        containerForImageAndUrlBoxAndButtons.classList.add('image-upload-multiple-box', 'f_image-element-main-box', 'f_oldImage');

        let firstDropzone = imageElement.closest('.f_1.f_multipleDropzone');
        containerForImageAndUrlBoxAndButtons.appendChild(imageElement);
        containerForImageAndUrlBoxAndButtons.appendChild(titleBox);
        firstDropzone.appendChild(containerForImageAndUrlBoxAndButtons);
    };

    isUploadFileValid(file, fileExtension) {
        if (file.size > this.MAX_UPLOAD_IMAGE_SIZE_IN_BYTES) {
            return false;
        }

        return fileExtension;
    }


    /**
     * show existing images
     * in multiple dropzone initializing, this function should be called only once
     * @param dropzoneUtilContext
     * @param isMultiple
     * @param dropzone
     */
    showExistingImages(_this, isMultiple, dropzone = null) {
        let existingImagesPaths = this.variables['imagesUrls'];

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

            if (this.variables.onlyOneDefaultImage) {
                let clickBtn = document.querySelector('.element-field .dz-remove');
                if (clickBtn) {
                    setTimeout(() => {
                        clickBtn.click();
                    }, 0);
                } else if (!this.variables.imagesHasWritePermission) {
                    let defaultImage = document.querySelector('.f_image-element-main-box');
                    if (defaultImage) {
                        defaultImage.remove();
                    }
                }
            }
        }

        if (isMultiple) {
            if (dropzone) {
                dropzone.removeEventListeners();
            }
            if (!this.variables.isViewMode) {
                this._initNewDropzoneAppearingUnderLastOne('f_1', false);
            }
        }

    };


    /**
     * initializing remove buttons correct working for both, new added and old existing images
     * @private
     */
    _initRemoveButtonsForMultiple() {
        let removeButtons = document.querySelectorAll('.dz-remove');
        let imageMainBoxes = [];

        removeButtons.forEach((button, buttonIndex) => {
            imageMainBoxes[buttonIndex] = removeButtons[buttonIndex].closest('.f_image-element-main-box');
            button.addEventListener('click', (evt) => {
                let hiddenInputWithCurrentImageProperties = evt.target.closest('.dz-image-preview')?.querySelector('.f_hidden-input-image-id');
                if (hiddenInputWithCurrentImageProperties) {
                    let clickedImageId = +hiddenInputWithCurrentImageProperties.value;
                    let indexOfCurrentImageIdInImagesArray = this.existingImagesIds.indexOf(clickedImageId);
                    if (indexOfCurrentImageIdInImagesArray !== -1) {
                        this.existingImagesIds[indexOfCurrentImageIdInImagesArray] = null;
                    }
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
    };


    _initRemoveButtonForSingle() {
        let removeButton = document.querySelector('.dz-remove');
        if (!removeButton) {
            return
        }

        removeButton.addEventListener('click', () => {
            this.existingImagesIds[0] = null;
        })

    };


    _arrayIsNotEmpty(existingImagesPaths) {
        if (existingImagesPaths === undefined || existingImagesPaths === [undefined] || existingImagesPaths[0] === undefined || existingImagesPaths[0] === [undefined]) {
            return false;
        }
        if (existingImagesPaths.every((i) => i === undefined || i === null)) {
            return false;
        }
        return true;
    };

    /**
     * each image that is new added should have image title box, and radio button
     * @param identifier
     * @param f_class
     */
    addTitleAndRadioButtonToImageForNewAddedImage(identifier, f_class) {
        let titleAndRadioButtonContainer = this.createTitleBox(identifier);
        let imageElementMainBox = document.createElement('div');
        imageElementMainBox.classList.add('image-upload-multiple-box', 'f_image-element-main-box', 'f_newAddedImage');

        let dzPreviewDropzone = document.querySelector('.' + f_class);


        let dzPreview = dzPreviewDropzone.querySelector('.dz-preview.dz-success');

        let currentDropzone = document.querySelector('.' + f_class);

        imageElementMainBox.appendChild(dzPreview);
        imageElementMainBox.appendChild(titleAndRadioButtonContainer);
        currentDropzone.appendChild(imageElementMainBox);
    };


    /**
     * there are 2 main elements that this function creates:  radio button in ints container, and copy button (with link) in its container
     * if is passed identifier only as argument, so its for new added image, and should not have url box and copy button box.
     * if are passed imageElement and imageIndex, so its for existing images, and should have url box and copy button box.
     *
     * @param identifier
     * @param imageElement
     * @param imageIndex
     * @returns {HTMLDivElement}
     */
    createTitleBox(identifier, imageElement = null, imageIndex = null) {
        let containerOfTitleAndButtons = document.createElement('div');
        containerOfTitleAndButtons.classList.add('info-box');
        let radioButtonContainer = document.createElement('div');
        // radioButtonContainer.classList.add('col-4');

        radioButtonContainer.appendChild(this.createIsMainRadioButton(imageIndex));
        containerOfTitleAndButtons.appendChild(radioButtonContainer);

        if (!identifier && imageElement) {  //for existing images
            containerOfTitleAndButtons.appendChild(this.createUrlOfImageAndCopyButton(imageIndex, this.variables['imagesUrls']));
        }

        return containerOfTitleAndButtons;
    };


    //todo: check this
    createIsMainRadioButton(index) {
        let isMainRadioButtonContainer = document.createElement('div');
        isMainRadioButtonContainer.classList.add('radiobox-item');
        let labelForIsMainRadioTextBox = document.createElement('div');
        labelForIsMainRadioTextBox.classList.add('text-item-box', 'medium1');
        labelForIsMainRadioTextBox.innerText = 'Main Image';

        if (this.variables.isViewMode || !this.variables.imagesHasWritePermission) {
            if (index !== null) {
                if (this.variables['imagesUrls'][index].isMain) {
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
        } else {

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
            labelForIsMainRadioBoxLabel.innerText = 'Use as:';

            isMainRadioButtonContainer.appendChild(labelForIsMainRadioBoxLabel);
            isMainRadioButtonContainer.appendChild(labelForIsMainRadio);

            if (index !== null) {
                if (this.variables['imagesUrls'][index].isMain) {
                    isMainRadioButton.checked = true;
                }

            }
        }

        return isMainRadioButtonContainer;
    };


    removeDefaultImageIfHasNoWritePermission() {
        if (this.variables.onlyOneDefaultImage && !this.variables.imagesHasWritePermission) {
            let imageContainer = document.querySelector('.f_all-dropzones-container');
            if (imageContainer) {
                let containerParent = imageContainer.closest('.form-item');
                if (containerParent) {
                    containerParent.remove();
                } else {
                    imageContainer.remove();
                }
            }
        }
    }


    /**
     * there are 2 times, that need to init new Dropzone under the main one:
     * 1: the first time, after showing existing images
     * 2: after each time some image is uploaded
     * @param f_class
     * @private
     */
    _initNewDropzoneAppearingUnderLastOne(f_class) {
        let allDropzonesContainer = document.querySelector(".f_all-dropzones-container");

        if (!allDropzonesContainer.querySelector('.f_image-element-main-box')) {
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
    };


    getDropzoneOptions(isMultiple, uploadFromPcBtn, dropzoneIdentifier) {
        let options = {
            url: "/target-url", // Set the url
            parallelUploads: 50,
            thumbnailHeight: 120,
            thumbnailWidth: 120,
            addRemoveLinks: true,
            dictDefaultMessage: "Upload Image",
            maxFilesize: 1,
            filesizeBase: 1000,
            autoProcessQueue: true,
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
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
        }

        if (isMultiple) {
            options.maxFilesize = 3;
            options.parallelUploads = 100;
            options.dictDefaultMessage = "Upload Multiple Images";
        }
        if (isMultiple) {
            if (dropzoneIdentifier && uploadFromPcBtn) {
                if (!this.initedClickables) {
                    this.initedClickables = {};
                }
                options.clickable = '.f_dropzone-hidden-input';
            }
        }

        return options;
    };

    initMulipleDropzoneStyles() {
        document.querySelector('.upload-image-left').classList.add('multiple-upload');
        document.querySelector('.form-items-container').classList.remove('no-flex-wrap');
    };


    getFileNameFromBlobType(type) {
        let extension = null;
        type = type.toLowerCase();

        switch (type) {
            case "image/png":
                extension = "png";
                break;
            case "image/jpeg":
                extension = "jpeg";
                break;
            case "image/jpg":
                extension = "jpg";
                break;
            case "image/gif":
                extension = "gif";
                break;
            case "":
                extension = "jpg";
                break;
        }
        return extension;
    };


    getUploadFromUrlhtml() {
        return "<div class=\"form-item \">\n" + "        <div class='input-field' style='text-align: left'>\n" + "             <label for='link' class=\"active\" >URL</label>\n" + "              <input id='link' name='link' class='form-text-control f_validate' type='text' placeholder='https:www.example.com' > \n" + "       </div>\n" + "  </div>"
    };


    /**
     * remove button of new added image should have unique identifier, to give possibility to remove yet not saved image
     * @param file
     * @param uniqueId
     * @private
     */
    _setUniqueIdToRemoveButton(file, uniqueId) {
        let removeButton = file.previewElement.querySelector('.dz-remove');
        removeButton.setAttribute('data-unique-id', uniqueId);
    };

    /**
     * return container of url of image with its label ("Link") and button for copy image url
     * @param inputTagForTitle
     * @returns {HTMLDivElement}
     */
    createUrlOfImageAndCopyButton(imageIndex, imageUrls) {
        let existingImagesPaths = imageUrls;

        let urlAndCopyButtonContainer = document.createElement('div');
        urlAndCopyButtonContainer.classList.add('url-box');

        let urlTextItem = document.createElement('div');
        urlTextItem.classList.add('url-text-item');

        let urlText = document.createElement('span');
        urlText.classList.add('url-text', 'medium1');
        urlText.innerHTML = `<a class="url-text-inner" href="${existingImagesPaths[imageIndex].url.original}" target="_blank">${existingImagesPaths[imageIndex].url.original}<a/>`;

        let labelForUrlLabel = document.createElement('div');
        labelForUrlLabel.classList.add('url-label', 't5');
        labelForUrlLabel.innerText = 'Link:';

        urlTextItem.appendChild(labelForUrlLabel);
        urlTextItem.appendChild(urlText);

        urlAndCopyButtonContainer.appendChild(urlTextItem);
        return urlAndCopyButtonContainer
    };


    /**
     * new dropzone element which is just a div, and need to be initialized yet
     * @param f_class
     * @returns {HTMLDivElement}
     * @private
     */
    _createNewDropzone(f_class) {
        let newDropzone = document.createElement('div');
        let newClass = this._createNewClass(f_class);
        newDropzone.classList.add('element-field', 'image-select-box', 'dropzone', newClass, 'f_multipleDropzone');
        return newDropzone;
    };


    /**
     * takes f_n as argument and returns f_n+1
     * @param f_class
     * @returns {string}
     * @private
     */
    _createNewClass(f_class) {
        let index = f_class.indexOf('_');
        let increment = +f_class.substr(index + 1) + 1;
        return 'f_' + increment;
    };


    /**
     * under each image creates a hidden input with properties of image
     * @param image
     * @returns {HTMLInputElement}
     */
    createHiddenInputWithCurrentImageProperties(image, onlyOneDefaultImage) {
        let hiddenInputWithProperties = document.createElement('input');

        let id = image.id;
        let thumbsInfo = {};

        if (!onlyOneDefaultImage) {
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
    };
};






