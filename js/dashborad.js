const elementotituloalerta = document.getElementById('tituloAlerta');
        const elementoboxtituloalerta = document.getElementById('boxtituloalerta');
        const larguratituloalerta = elementotituloalerta.offsetWidth;
        const larguraboxtituloalerta = elementoboxtituloalerta.offsetWidth;
        elementotituloalerta.style.setProperty('--larguratituloalerta', larguratituloalerta + 'px');
        elementotituloalerta.style.setProperty('--larguraboxtituloalerta', larguraboxtituloalerta + 'px');

        function abrircriaralerta() {
            document.getElementById("popcriaralerta").style.display = "block";
            document.getElementById("criaralertaoverlay").style.display = "block";
        }

        // Função para fechar o pop-up e a sobreposição
        function fecharcriaralerta() {
            document.getElementById("popcriaralerta").style.display = "none";
            document.getElementById("criaralertaoverlay").style.display = "none";
        }

        

        function abrirveralerta() {
            document.getElementById("popmostraralerta").style.display = "block";
            document.getElementById("mostraralertaoverlay").style.display = "block";
        }

        // Função para fechar o pop-up e a sobreposição
        function fecharveralerta() {
            document.getElementById("popmostraralerta").style.display = "none";
            document.getElementById("mostraralertaoverlay").style.display = "none";
        }