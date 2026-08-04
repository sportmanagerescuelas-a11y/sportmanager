(function () {
  function initGoldCardPreview() {
    var card = document.querySelector('.gold-card');
    if (!card) {
      return;
    }

    var nombres = document.getElementById('nombres');
    var apellidos = document.getElementById('apellidos');
    var categoria = document.getElementById('categoria');
    var fechaNacimiento = document.getElementById('fecha_nacimiento');
    var jornada = document.querySelector('select[name="jornada"]');
    var genero = document.getElementById('genero');
    var foto = document.getElementById('foto');
    var previewNombre = card.querySelector('.gold-card__name');
    var previewGenero = card.querySelector('.gold-card__meta > div:nth-child(1)');
    var previewJornada = card.querySelector('.gold-card__meta > div:nth-child(2)');
    var previewCategoria = card.querySelector('.gold-card__meta > div:nth-child(3)');
    var previewFoto = card.querySelector('.gold-card__slot img');

    function birthDateToCategoryLabel(value) {
      if (!value) {
        return '';
      }

      var birth = new Date(value + 'T12:00:00');
      if (isNaN(birth.getTime())) {
        return '';
      }

      var today = new Date();
      var age = today.getFullYear() - birth.getFullYear();
      var monthDiff = today.getMonth() - birth.getMonth();
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
      }

      if (age <= 6) return 'sub-7';
      if (age <= 8) return 'sub-9';
      if (age <= 10) return 'sub-11';
      if (age <= 12) return 'sub-13';
      if (age <= 14) return 'sub-15';
      if (age <= 16) return 'sub-17';
      return 'sub-19';
    }

    function syncLabels() {
      var categoryLabel = birthDateToCategoryLabel(fechaNacimiento ? fechaNacimiento.value : '');

      if (previewNombre && nombres && apellidos) {
        previewNombre.innerText = (nombres.value + ' ' + apellidos.value).trim() || 'Tu nombre';
      }
      if (previewGenero && genero) {
        previewGenero.innerText = genero.value || 'Genero';
      }
      if (previewJornada && jornada) {
        previewJornada.innerText = jornada.options[jornada.selectedIndex] && jornada.options[jornada.selectedIndex].text ? jornada.options[jornada.selectedIndex].text : 'Jornada';
      }
      if (previewCategoria) {
        previewCategoria.innerText = categoryLabel || 'Categoria';
      }
      if (categoria) {
        var optionIndex = -1;
        for (var i = 0; i < categoria.options.length; i++) {
          if ((categoria.options[i].text || '').trim().toLowerCase() === categoryLabel.toLowerCase()) {
            optionIndex = i;
            break;
          }
        }

        if (optionIndex >= 0) {
          categoria.selectedIndex = optionIndex;
        } else if (categoria.options.length > 0) {
          categoria.selectedIndex = 0;
        }
      }
    }

    if (nombres) {
      ['input', 'change'].forEach(function (eventName) {
        nombres.addEventListener(eventName, syncLabels);
      });
    }
    if (apellidos) {
      ['input', 'change'].forEach(function (eventName) {
        apellidos.addEventListener(eventName, syncLabels);
      });
    }
    if (categoria) {
      ['input', 'change'].forEach(function (eventName) {
        categoria.addEventListener(eventName, syncLabels);
      });
    }
    if (fechaNacimiento) {
      ['input', 'change'].forEach(function (eventName) {
        fechaNacimiento.addEventListener(eventName, syncLabels);
      });
    }
    if (jornada) {
      ['input', 'change'].forEach(function (eventName) {
        jornada.addEventListener(eventName, syncLabels);
      });
    }
    if (genero) {
      ['input', 'change'].forEach(function (eventName) {
        genero.addEventListener(eventName, syncLabels);
      });
    }

    if (foto && previewFoto) {
      foto.addEventListener('change', function (e) {
        var file = e.target.files && e.target.files[0];
        if (file) {
          if (previewFoto.dataset.objectUrl) {
            URL.revokeObjectURL(previewFoto.dataset.objectUrl);
          }
          var objectUrl = URL.createObjectURL(file);
          previewFoto.dataset.objectUrl = objectUrl;
          previewFoto.src = objectUrl;
        } else {
          previewFoto.src = 'fotos/default.png';
        }
      });
    }

    syncLabels();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGoldCardPreview);
  } else {
    initGoldCardPreview();
  }
})();
