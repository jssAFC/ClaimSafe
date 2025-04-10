//  Function for dropdown menu
function showSidebar(){
  const sidebar=document.querySelector('.sidebar');
  sidebar.classList.remove('hidden');
  const body=document.querySelector('body');
  body.classList.add('bg-white/40');
}

function hideSidebar(){
  const sidebar=document.querySelector('.sidebar');
  sidebar.classList.add('hidden')
}

//  Function for sliding cards
let count = 1;
const heading = document.getElementById("card-heading");
const text = document.getElementById("card-text");
const headings = [
  "Own Damage Car Insurance",
  "Comprehensive Car Insurance",
  "Third-Party Car Insurance",
];

const content = [
  "This policy covers damage to your vehicle from natural disasters like floods and earthquakes, as well as man-made incidents such as theft, riots, and accidents involving uninsured drivers.",
  "Third-Party Car Insurance is required by law in India, this basic coverage protects against legal liabilities for third-party bodily injuries, property damage, or death caused by your vehicle. However, it does not cover damage that is done to your own vehicle.",
  "This is the most extensive coverage, offering proper protection against third-party liabilities and damage to your vehicle caused by accidents, natural calamities, theft, and fire. It also includes hospitalisation costs for drivers and passengers, towing charges, and personal accident cover.",
];

const leftClick = document.getElementById("left-button");
const rightClick = document.getElementById("right-button");
function leftSlide() {
  count--;
  if (count == 0) {
    leftClick.classList.add("hidden");
    rightClick.classList.remove("hidden");
    heading.textContent = headings[0];
    text.textContent = content[0];
  } else {
    leftClick.classList.remove("hidden");
    rightClick.classList.remove("hidden");
    heading.textContent = headings[1];
    text.textContent = content[1];
  }
}

function rightSlide() {
  count++;
  if (count == 1) {
    leftClick.classList.remove("hidden");
    rightClick.classList.remove("hidden");
    heading.textContent = headings[1];
    text.textContent = content[1];
  } else {
    leftClick.classList.remove("hidden");
    rightClick.classList.add("hidden");
    heading.textContent = headings[2];
    text.textContent = content[2];
  }
}

let count2 = 2;
const heading2 = document.getElementById("card-heading2");
const text2 = document.getElementById("card-text2");
const headings2 = [
  "Personal Accident Cover",
  "Third Party Liabilities",
  "Damage Loss",
  "No Claim Bonus",
  "Roadside Assistance",
];

const content2 = [
  "Personal Accident Cover provides financial protection to the vehicle owner/driver in case of accidental death or disability. This coverage ensures compensation for unfortunate events while driving.",
  "Third Party Liability coverage protects you against legal and financial liabilities arising from injury, death, or property damage caused to third parties by your insured vehicle.",
  "Damage Loss coverage protects your vehicle against damages from accidents, theft, fire, natural calamities, and other unforeseen events. It covers the cost of repairs or replacement.",
  "No Claim Bonus is a reward in the form of discount on premium for not making any claims during the policy period. The discount increases progressively for each claim-free year.",
  "Roadside Assistance provides 24/7 emergency services including towing, battery jump-start, flat tire change, fuel delivery, and mechanical assistance when your vehicle breaks down.",
];

const images = [
  "./assets/personal_loss.webp",
  "./assets/third_party.webp",
  "./assets/accident_image.webp",
  "./assets/no_claim.webp",
  "./assets/raodside_assistance.webp",
];

const leftClick2 = document.getElementById("left-button-2");
const rightClick2 = document.getElementById("right-button-2");
const cardImage = document.getElementById("card-image");
function leftSlide2() {
  count2--;
  if (count2 == 0) {
    leftClick2.classList.add("hidden");
    rightClick2.classList.remove("hidden");
    cardImage.src = images[0];
    heading2.textContent = headings2[0];
    text2.textContent = content2[0];
  } else if (count2 == 1) {
    leftClick2.classList.remove("hidden");
    rightClick2.classList.remove("hidden");
    cardImage.src = images[1];
    heading2.textContent = headings2[1];
    text2.textContent = content2[1];
  } else if (count2 == 2) {
    leftClick2.classList.remove("hidden");
    rightClick2.classList.remove("hidden");
    cardImage.src = images[2];
    heading2.textContent = headings2[2];
    text2.textContent = content2[2];
  } else {
    leftClick2.classList.remove("hidden");
    rightClick2.classList.remove("hidden");
    cardImage.src = images[3];
    heading2.textContent = headings2[3];
    text2.textContent = content2[3];
  }
}

function rightSlide2() {
  count2++;
  if (count2 == 1) {
    leftClick2.classList.remove("hidden");
    rightClick2.classList.remove("hidden");
    heading2.textContent = headings2[1];
    cardImage.src = images[1];
    text2.textContent = content2[1];
  } else if (count2 == 2) {
    leftClick2.classList.remove("hidden");
    rightClick2.classList.remove("hidden");
    cardImage.src = images[2];
    heading2.textContent = headings2[2];
    text2.textContent = content2[2];
  } else if (count2 == 3) {
    leftClick2.classList.remove("hidden");
    rightClick2.classList.remove("hidden");
    cardImage.src = images[3];
    heading2.textContent = headings2[3];
    text2.textContent = content2[3];
  } else {
    leftClick2.classList.remove("hidden");
    rightClick2.classList.add("hidden");
    cardImage.src = images[4];
    heading2.textContent = headings2[4];
    text2.textContent = content2[4];
  }
}

function buttons() {
  const parentSlide = document.getElementById("parentSlide");
  const text = [
    "Enter your personal information, including name, address, contact details, and vehicle information to initiate the registration process.",
    "Provide necessary documentation such as your driver's license, vehicle registration, and insurance policy details for verification.",
    "Submit details about your medical expenses, vehicle damage, and any third-party claims related to the accident.",
    "Review and confirm your claim submission, after which our team will process your request and contact you for further steps.",
  ];

  function slideCards(event) {
    const target = event.target.id;
    console.log(target);

    const divMain = document.createElement("div");
    // parentSlide.innerHTML = "";
    divMain.classList.add("w-[85%]");
    divMain.classList.add("h-72",);
    divMain.classList.add("flex");
    divMain.classList.add("justify-center", "rounded-3xl");
    divMain.classList.add("gap-4");
    divMain.classList.add("p-8");
    divMain.classList.add(
      "mx-auto",
      "bg-purple-500",
      "flex",
      "gap-2",
      "flex-col",
      "absolute",
      'z-10',

    );

    if (target == "slide1") {
      const imageText = document.createElement("div");

      const image = document.createElement("img");
      image.src = "./assets/steps1.webp";
      image.classList.add("object-cover", "h-16", "w-16", "self-start","md:h-24","md:w-24");

      const imgHead = document.createElement("h3");
      imgHead.classList.add("text-3xl", "font-bold", "text-white","lg:text-5xl");
      imgHead.textContent = "Step-1";

      imageText.classList.add("flex", "gap-4", "items-center");
      imageText.append(image, imgHead);

      const textInside = document.createElement("p");
      textInside.classList.add("text-xl", "text-white","md:text-2xl",'font-md');
      textInside.textContent = text[0];

      const cross = document.createElement("button");
      cross.id="cross";
      cross.textContent = "X";
      cross.classList.add(
        "top-5",
        "right-5",
        "absolute",
        "text-white",
        "font-bold",
        "hover:pointer"
      );

      divMain.append(imageText, textInside, cross);
      cross.addEventListener('click',()=>{
        parentSlide.removeChild(divMain);
      })
    }

    else if (target == "slide2") {
      const imageText = document.createElement("div");

      const image = document.createElement("img");
      image.src = "./assets/steps2.webp";
      image.classList.add("object-cover", "h-16", "w-16", "self-start","md:h-24","md:w-24");

      const imgHead = document.createElement("h3");
      imgHead.classList.add("text-3xl", "font-bold", "text-white","lg:text-5xl");
      imgHead.textContent = "Step-2";

      imageText.classList.add("flex", "gap-4", "items-center");
      imageText.append(image, imgHead);

      const textInside = document.createElement("p");
      textInside.classList.add("text-xl", "text-white","md:text-2xl",'font-md');
      textInside.textContent = text[1];

      const cross = document.createElement("button");
      cross.id="cross";
      cross.textContent = "X";
      cross.classList.add(
        "top-5",
        "right-5",
        "absolute",
        "text-white",
        "font-bold"
      );

      divMain.append(imageText, textInside, cross);
      cross.addEventListener('click',()=>{
        parentSlide.removeChild(divMain);
      })
    }

    else if (target == "slide3") {
      const imageText = document.createElement("div");

      const image = document.createElement("img");
      image.src = "./assets/steps3.webp";
      image.classList.add("object-cover", "h-16", "w-16", "self-start","md:h-24","md:w-24");

      const imgHead = document.createElement("h3");
      imgHead.classList.add("text-3xl", "font-bold", "text-white","lg:text-5xl");
      imgHead.textContent = "Step-3";

      imageText.classList.add("flex", "gap-4", "items-center");
      imageText.append(image, imgHead);

      const textInside = document.createElement("p");
      textInside.classList.add("text-xl", "text-white","md:text-2xl",'font-md');
      textInside.textContent = text[2];

      const cross = document.createElement("button");
      cross.id="cross";
      cross.textContent = "X";
      cross.classList.add(
        "top-5",
        "right-5",
        "absolute",
        "text-white",
        "font-bold"
      );

      divMain.append(imageText, textInside, cross);
      cross.addEventListener('click',()=>{
        parentSlide.removeChild(divMain);
      })
    }

    else if (target == "slide4") {
      const imageText = document.createElement("div");

      const image = document.createElement("img");
      image.src = "./assets/steps4.webp";
      image.classList.add("object-cover", "h-16", "w-16", "self-start","md:h-24","md:w-24");

      const imgHead = document.createElement("h3");
      imgHead.classList.add("text-3xl", "font-bold", "text-white","lg:text-5xl");
      imgHead.textContent = "Step-4";

      imageText.classList.add("flex", "gap-4", "items-center");
      imageText.append(image, imgHead);

      const textInside = document.createElement("p");
      textInside.classList.add("text-xl", "text-white","md:text-2xl",'font-md');
      textInside.textContent = text[3];

      const cross = document.createElement("button");
      cross.id="cross";
      cross.textContent = "X";
      cross.classList.add(
        "top-5",
        "right-5",
        "absolute",
        "text-white",
        "font-bold"
      );

      divMain.append(imageText, textInside, cross);
      cross.addEventListener('click',()=>{
        parentSlide.removeChild(divMain);
      })
    }

    parentSlide.append(divMain);
  }

  parentSlide.addEventListener("click", slideCards(event));
}


function toggleAnswer(element){
  const answer=element.nextElementSibling;
  answer.classList.toggle('hidden');

  const button=element.querySelector('#toggle-btn');
  button.textContent=answer.classList.contains('hidden')?"+":"-";
}
