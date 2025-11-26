// Inicialização do trem
document.addEventListener('DOMContentLoaded', function () {
    const sleepersContainer = document.querySelector('.train-sleepers');
    if (sleepersContainer) {
        const trackWidth = document.querySelector('.train-track').offsetWidth;
        const sleeperCount = Math.ceil(trackWidth / 35) + 2;

        for (let i = 0; i < sleeperCount; i++) {
            const sleeper = document.createElement('div');
            sleeper.className = 'train-sleeper';
            sleeper.style.left = `${i * 35}px`;
            sleepersContainer.appendChild(sleeper);
        }

        const rail = document.querySelector('.train-rail');
        const sleepers = document.querySelector('.train-sleepers');
        let animationState = 'stopped';
        let speed = 'fast';

        const btnLeft = document.getElementById('goLeft');
        const btnStop = document.getElementById('stop');
        const btnRight = document.getElementById('goRight');

        function updateAnimation() {
            rail.classList.remove('stopped', 'fast', 'reverse');
            sleepers.classList.remove('stopped', 'fast', 'reverse');

            if (animationState === 'stopped') {
                rail.classList.add('stopped');
                sleepers.classList.add('stopped');
            } else {
                rail.classList.add(speed);
                sleepers.classList.add(speed);

                if (animationState === 'left') {
                    rail.classList.add('reverse');
                    sleepers.classList.add('reverse');
                }
            }
        }

        btnLeft.addEventListener('click', () => {
            animationState = 'left';
            updateAnimation();
        });

        btnStop.addEventListener('click', () => {
            animationState = 'stopped';
            updateAnimation();
        });

        btnRight.addEventListener('click', () => {
            animationState = 'right';
            updateAnimation();
        });
        updateAnimation();
    }
});