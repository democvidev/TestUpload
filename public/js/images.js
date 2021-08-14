//on attend que le DOM soit chargé
window.onload = () => {
  //recupérer les références aux noeuds
  let links = document.querySelectorAll('[data-delete]')
  //boucler sur links
  for (link of links) {
    // écouter le click
    link.addEventListener('click', function (e) {
      //empécher la le fonctionnement par défaut
      e.preventDefault()
      //confirmation avec le pop up
      if (confirm('Voulez-vous supprimer cette image ?')) {
        //requête ajax vers le href du lien avec la méthode DELETE, sous forme d'une promesse
        fetch(this.getAttribute('href'), {
          method: 'DELETE',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ _token: this.dataset.token }),
        })
          .then(
            // On récupère la réponse en JSON
            (response) => response.json(),
          )
          .then((data) => {
            if (data.success) this.parentElement.remove()
            else alert(data.error)
          })
          .catch((e) => alert(e))
      }
    })
  }
}
