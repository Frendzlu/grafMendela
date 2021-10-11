let CURRENT: string = "0"
function showModal(e: any) {
    e.preventDefault()
    let el = document.getElementById("formDiv")!
    el.style.display = "block"
    CURRENT = e.target.id
}
function hideModal(){
    let el = document.getElementById("formDiv")!
    el.style.display = "none"
    CURRENT = ""
}
function validate(e: any){
    let str = e.target.value
    let regex = /[^0-9.]*/ig
    e.target.value = str.replaceAll(regex, "")
}
async function setNull(e: Event) {
    await fetch(`change.php?day=${CURRENT}&value=${null}`, {
        method: "GET",
    })
    await update()
    hideModal()
}
async function setTemp(e: Event) {
    let input = document.getElementById("tempInput") as HTMLInputElement
    console.log(input)
    await fetch(`change.php?day=${CURRENT}&value=${input.value}`, {
        method: "GET",
    })
    await update()
    hideModal()
}
async function setIll(e: Event) {
    await fetch(`change.php?day=${CURRENT}&value=${0}`, {
        method: "GET",
    })
    await update()
    hideModal()
}

async function update(){
    let el = document.getElementById(CURRENT) as HTMLAreaElement
    let img = document.getElementById("uwu") as HTMLImageElement
    const urlSearchParams = new URLSearchParams(window.location.search) as any;
    let str = "./image.php" + "?justToRefresh=" + Date.now()
    for (const [p, value] of urlSearchParams.entries()){
        str += `&${p}=${value}`
    }
    let height = urlSearchParams.get("height") ? urlSearchParams.get("height") : 300
    let width = urlSearchParams.get("width") ? urlSearchParams.get("width") : 800
    console.log(height, width)
    img.src = str
    let data = await fetch('getData.php', {
        method: "GET"
    })
        .then(response => response.json())
    for (const day of data[0]){
        if (day[2] == el.id){
            console.log(el)
            console.log((day[1]/300)*height)
            el.coords = `${(day[0]/800)*width},${(day[1]/300)*height},${data[1]}`
            console.log(el)
            break
        }
    }
}