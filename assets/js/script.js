const modal = document.querySelector('.modal');
const modalTitle = document.querySelector('#modal-title');
const dhikrTitle = document.querySelector('#dhikr-title');
const dhikrText = document.querySelector('#dhikr-text');
const dhikrTranslation = document.querySelector('#dhikr-translation');
const counterButton = document.querySelector('.counter-button');

let openedCollection = null;
let openedDhikr = null;
let counter = 0;
let dhikrIndex = 0;

const injectCollectionDataIntoModal = (collection) => {
    updateCounterLabelValue();
    modalTitle.textContent = collection.name;
    injectDhikrDataIntoModal();
}

const injectDhikrDataIntoModal = () => {
    dhikrTitle.textContent = openedDhikr.name;
    dhikrText.textContent = openedDhikr.text;
    dhikrTranslation.textContent = openedDhikr.translation;
}

const count = () => {
    counter++;
    updateCounterLabelValue();
    if (counter >= openedDhikr.count) {
        gotoNextDhikr();
    }
}

const thereIsANextDhikr = () => {
    return openedCollection.adhkar[dhikrIndex + 1] !== undefined;
}

const gotoNextDhikr = () => {
    counter = 0;
    if (thereIsANextDhikr()){
        openedDhikr = openedCollection.adhkar[++dhikrIndex];
        updateCounterLabelValue();
        injectDhikrDataIntoModal();
    }
    else{
        closeModal();
    }
}

const updateCounterLabelValue = () => {
    counterButton.textContent = counter;
}

const openModal = (collection) => {
    openedCollection = collection;
    openedDhikr = collection.adhkar[dhikrIndex];
    counter = 0;
    injectCollectionDataIntoModal(collection);
    modal.classList.remove('hidden');
};

const closeModal = () => {
    modal.classList.add('hidden');
};

