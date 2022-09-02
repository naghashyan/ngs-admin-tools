import Choices from "../lib/choices.min.js";

let ChoiceManager = {

  init: function (container) {
    this.choices = {};
    let choicesElems = container.querySelectorAll('.ngs-choice');
    for (let i = 0; i < choicesElems.length; i++) {
      let choiceElem = choicesElems[i];
        if(choiceElem.choices) {
            continue;
        }
        choiceElem.choices = new Choices(choiceElem,
            {
                removeItemButton: choiceElem.getAttribute('data-ngs-remove') === 'true',
                searchEnabled: choiceElem.getAttribute('data-ngs-searchable') === 'true',
                renderChoiceLimit: 150,
                searchResultLimit: 150,
                shouldSort: !choiceElem.getAttribute('data-do-not-sort'),
            });
    }
  }
};
export default ChoiceManager;
