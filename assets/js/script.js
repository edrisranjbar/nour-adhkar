const modal = document.querySelector('.modal');
const modalContent = document.querySelector('.modal-container');
const modalTitle = document.querySelector('#modal-title');
const dhikrTitle = document.querySelector('#dhikr-title');
const progressIndicator = document.querySelector('.progressbar-fill');
const dhikrText = document.querySelector('#dhikr-text');
const dhikrCount = document.querySelector('#dhikr-count');
const dhikrProgressDetails = document.querySelector('#dhikr-progress-details');
const dhikrTranslation = document.querySelector('#dhikr-translation');
const counterButton = document.querySelector('.counter-button');

const tapSoundAudioPath = 'assets/audios/click.mp3';

let totalProgress = 0;
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
    dhikrCount.textContent = `${openedDhikr.count} مرتبه`;
    dhikrProgressDetails.textContent = `${counter} از ${openedDhikr.count} مرتبه`;
    dhikrTitle.textContent = openedDhikr.name;
    dhikrText.innerHTML = openedDhikr.text;
    dhikrTranslation.textContent = openedDhikr.translation;
}

const count = (event) => {
    if (event && event.target.id == 'share-button') { return }
    counter++;
    updateCounterLabelValue();
    if (counter >= openedDhikr.count) {
        gotoNextDhikr();
    }
    playAudio(tapSoundAudioPath);
}

const thereIsANextDhikr = () => {
    return openedCollection.adhkar[dhikrIndex + 1] !== undefined;
}

const gotoNextDhikr = () => {
    counter = 0;
    if (thereIsANextDhikr()) {
        vibrate();
        openedDhikr = openedCollection.adhkar[++dhikrIndex];
        updateCounterLabelValue();
        injectDhikrDataIntoModal();
        updateTotalProgress();
    }
    else {
        dhikrIndex = 0;
        closeModal();
    }
}

const updateCounterLabelValue = () => {
    counterButton.textContent = counter;
    dhikrProgressDetails.textContent = `${counter} از ${openedDhikr.count} مرتبه`;
}

const openModal = (collection) => {
    openedCollection = collection;
    openedDhikr = collection.adhkar[dhikrIndex];
    counter = 0;
    updateTotalProgress();
    injectCollectionDataIntoModal(collection);
    modal.classList.remove('hidden');
    document.body.style.overflowY = 'hidden';
}

const closeModal = () => {
    modal.classList.add('hidden');
    document.body.style.overflowY = 'unset';
}

const share = () => {
    console.log('Copy text');
}

const updateTotalProgress = () => {
    progressIndicator.style.width = `${calculateTotalProgress()}%`;
}

const calculateTotalProgress = () => {
    const total = openedCollection.adhkar.length;
    const currentDhikrIndex = openedCollection.adhkar.findIndex((element) => element === openedDhikr) + 1;
    return totalProgress = Math.max((currentDhikrIndex / total) * 100, 5);
}

const playAudio = (audioPath) => {
    const audio = new Audio(audioPath);
    audio.play();
}

const vibrate = () => {
    window.navigator.vibrate([400])
}

// Event listener
modalContent.addEventListener('click', (event) => { count(event) });