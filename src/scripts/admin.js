;(function() {
  const checkboxes = document.querySelectorAll(".cb-save")

  if (!checkboxes) {
    console.log("No checkboxes found on this page.")
    return false
  }

  checkboxes.forEach(cb => {
    cb.addEventListener("click", save_cb)
  })

  function save_cb(event) {
    var action = "save_meta"
    var nonce = event.target.getAttribute("data-nonce")
    var name = event.target.name
    var value = event.target.value
    var checked = event.target.checked

    event.target.classList.add("loading")

    save_data(
      wp.ajax_url,
      `action=${action}&_wpnonce=${nonce}&key=${name}&value=${checked}&post_id=${value}`,
    )
      .then(resp => {
        resp.ok
          ? event.target.classList.replace("loading", "done")
          : event.target.classList.replace("loading", "error")
        return resp.json()
      })
      .then(json => console.log(json))
  }

  async function save_data(url = "", data) {
    return fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=utf-8",
      },
      credentials: "same-origin",
      body: typeof data === "object" ? JSON.stringify(data) : data,
    })
  }
})()
