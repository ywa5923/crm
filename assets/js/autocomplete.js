function autocomplete(ajaxUrl, inputId, ulId, relatedValueId, zipCodeInputId) {
  const searchBox = document.getElementById(inputId);
  const ulElement = document.getElementById(ulId);
  
  searchBox.onkeyup = function () {
    let searchResults = [];
    const relatedValue = document.getElementById(relatedValueId).value;
    let searchString = this.value;
    ulElement.innerHTML = "";
    let url = ajaxUrl + "?search=" + searchString + "&related=" + relatedValue;
    console.log(url);
    fetch(url)
      .then((response) => response.json())
      .then((data) =>
        data.forEach((data) => {
          //console.log("id: ", data.name, "year: ", data.auto);
          ulElement.insertAdjacentHTML(
            "beforeend",
            `<li style= "padding:10px" onclick=selectInput(this,"${inputId}","${ulId}","${zipCodeInputId}")>` +
              data.name +
              "-" +
              data.zip +
              "</li>"
          );
        })
      );
  };
}
function selectInput(listItem, inputId, ulId, zipCodeInputId) {
  const searchBox = document.getElementById(inputId);

  const [locality, zipCode] = listItem.innerHTML.split("-");
  searchBox.value = locality;
  const ul = document.getElementById(ulId);

  const zipCodeEl = document.getElementById(zipCodeInputId);
  if (zipCodeEl !== "undefined" && zipCodeEl !== null) {
    console.log(zipCodeEl);
    zipCodeEl.value = zipCode;
  }

  ul.innerHTML = "";
}
