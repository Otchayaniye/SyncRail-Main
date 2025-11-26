// Inicialização do trem
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar dormentes aos trilhos
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
        
        // Elementos da animação
        const rail = document.querySelector('.train-rail');
        const sleepers = document.querySelector('.train-sleepers');
        let animationState = 'stopped'; // stopped, left, right
        let speed = 'slow'; // slow, normal, fast
        
        // Botões
        const btnLeft = document.getElementById('goLeft');
        const btnStop = document.getElementById('stop');
        const btnRight = document.getElementById('goRight');
        
        // Função para atualizar animação
        function updateAnimation() {
            // Reset todas as classes
            rail.classList.remove('stopped', 'slow', 'fast', 'reverse');
            sleepers.classList.remove('stopped', 'slow', 'fast', 'reverse');
            
            if (animationState === 'stopped') {
                rail.classList.add('stopped');
                sleepers.classList.add('stopped');
            } else {
                // Velocidade
                rail.classList.add(speed);
                sleepers.classList.add(speed);
                
                // Direção
                if (animationState === 'left') {
                    rail.classList.add('reverse');
                    sleepers.classList.add('reverse');
                }
            }
            
            // Atualizar estado dos botões
            updateButtonStates();
        }
        
        // Função para atualizar estados dos botões
        function updateButtonStates() {
            // Remover classe active de todos
            btnLeft.classList.remove('active');
            btnStop.classList.remove('active');
            btnRight.classList.remove('active');
            
            // Adicionar classe active ao botão correspondente
            if (animationState === 'left') {
                btnLeft.classList.add('active');
            } else if (animationState === 'right') {
                btnRight.classList.add('active');
            } else {
            }
        }
        
        // Event listeners para os botões
        btnLeft.addEventListener('click', () => {
            if (animationState === 'left') {
                // Se já está indo para esquerda, aumenta velocidade
                if (speed === 'slow') speed = 'normal';
                else if (speed === 'normal') speed = 'fast';
            } else {
                animationState = 'left';
                speed = 'slow';
            }
            updateAnimation();
        });
        
        btnStop.addEventListener('click', () => {
            animationState = 'stopped';
            updateAnimation();
        });
        
        btnRight.addEventListener('click', () => {
            if (animationState === 'right') {
                // Se já está indo para direita, aumenta velocidade
                if (speed === 'slow') speed = 'normal';
                else if (speed === 'normal') speed = 'fast';
            } else {
                animationState = 'right';
                speed = 'slow';
            }
            updateAnimation();
        });
        
        // Inicializar animação parada
        updateAnimation();
    }
});