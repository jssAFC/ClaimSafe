<?php include('includes/header.php'); ?>

<!-- <body > -->
<!-- -----------Nav bar------------- -->

<!-- PC -->

<nav
  class="bg-white h-16 flex items-center px-[5vw] shadow-md justify-between max-md:hidden fixed left-0 right-0 top-0 z-40">
  <div class="max-w-1/2 flex gap-10">
    <h1
      class="inline-block text-purple-700 text-2xl font-extrabold font-sans">
      ClaimSafe
    </h1>
    <ul class="flex gap-[1rem] items-center font-semibold">
      <li class="hover:text-purple-700"><a href="#home">Home</a></li>
      <li class="hover:text-purple-700"><a href="#aboutUs">About Us</a></li>
      <li class="hover:text-purple-700"><a href="#procedure">Procedure</a></li>
      <li class="hover:text-purple-700"><a href="#faq">FAQs</a></li>
    </ul>
  </div>

  <div class="flex items-center gap-4 justify-center">
    <a
      href="./pages/login.php" target="_blank"
      class="text-sm bg-purple-600 text-white p-2 rounded-2xl font-bold">Log In/Sign Up</a>
    <a href="https://claimsafe.onrender.com" class="flex items-center gap-1 hover:text-purple-700">
      <p class="font-semibold text-sm">Need Help</p>
      <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        class="size-6">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
      </svg>
    </a>
  </div>
</nav>

<!-- Mobile -->
<nav class="bg-white h-16 flex px-[5vw] shadow-lg md:hidden justify-between items-center fixed top-0 right-0 left-0 z-40">

  <!-- Hamburger -->
  <li class=" list-none" onclick="showSidebar()">

      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
      </svg>

  </li>

  <!-- Heading -->
  <h1
    class=" text-purple-700 text-2xl font-extrabold font-sans text-center mx-auto">
    ClaimSafe
  </h1>

  <!-- sidebar -->

  <div class="fixed top-0 left-0 h-[100vh] w-[200px] z-50 flex flex-col items-start justify-start md:hidden list-none p-4 bg-white shadow-2xl sidebar hidden">
    <li class="self-end" onclick="hideSidebar()">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
    </li>
    <li class="w-full border-b-2 border-gray-300 px-1"><a class="w-full hover:bg-white" href="#">Home</a></li>
    <li class="w-full border-b-2 border-gray-300 px-1"><a class="w-full hover:bg-white" href="#aboutUs">About</a></li>
    <li class="w-full border-b-2 border-gray-300 px-1"><a class="w-full hover:bg-white" href="#procedure">Procedure</a></li>
    <li class="w-full border-b-2 border-gray-300 px-1"><a class="w-full hover:bg-white" href="#faq">FAQs</a></li>
    <li class="w-full border-b-2 border-gray-300 px-1"><a href="https://claimsafe.onrender.com/" class="flex items-center gap-1 w-full hover:text-purple-700">
        <p>Support</p>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          class="size-5 text-white">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
        </svg>
      </a></li>
    <br><br>
    <li><a
        href="./pages/login.php" target="_blank"
        class="text-sm bg-purple-600 text-white  min-w-28 rounded-md p-2 px-3 font-medium text-center">Log In/Sign-Up</a>
    </li>

  </div>

</nav>

<!-- Main content -->
<main class="pt-24" id="home">
  <h1 class="text-2xl md:text-4xl font-bold text-center mt-5 p-4 pb-8">
    Accident Insaurence Within Minutes
  </h1>
<!-- Main content -->
<main class="pt-24" id="home">
  <h1 class="text-2xl md:text-4xl font-bold text-center mt-5 p-4 pb-8">
    Accident Insaurence Within Minutes
  </h1>

  <!-- statistics -->
  <div
    class="w-[95vw] h-auto bg-purple-200 p-4 mx-auto rounded-xl flex justify-around mt-5 border-r-2">
    <div class="flex items-center justify-center flex-col flex-wrap gap-1">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        class="size-6 md:size-10">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
      </svg>
      <p class="font-bold text-sm md:text-xl">1,12,000+</p>
      <p class="text-[10px] md:text-sm">Customer Served</p>
    </div>
    <div class="flex items-center justify-center flex-col flex-wrap gap-1">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        class="size-6 md:size-10">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
      </svg>

      <p class="font-bold text-sm md:text-xl">96%+</p>
      <p class="text-[10px] md:text-sm">Claims Settled</p>
    </div>
    <div class="flex items-center justify-center flex-col flex-wrap gap-1">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        class="size-6 md:size-10">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
      </svg>

      <p class="font-bold text-sm md:text-xl">4.7</p>
      <p class="text-[10px] md:text-sm">Customer Rating</p>
    </div>
  </div>

  <!-- car insaurence -->
  <div class="bg-gray-200 max-w-full my-8 p-4" id="aboutUs">
    <h2 class="font-bold text-center text-xl p-8 max-md:p-4 md:text-3xl">
      About Us
    </h2>
    <div
      class="flex max-md:flex-col justify-around items-center p-4 gap-[10%]">
      <img
        src="./assets/aggrement-image.webp"
        alt=""
        class="w-3/4 max-sm:h-36 h-44 object-fit relative left-6 " />
      <p class="p-4 text-sm font-sans text-wrap sm:text-base text-justify">
        Whether you have to commute to work or need a vehicle to get around
        the city, having a car is a dream come true. With a car to your
        name, you can travel from one place to another at your convenience
        and in the utmost comfort. However, your car is susceptible to
        potential damage, loss, theft, and other misfortunes, and replacing
        or repairing your car comes with a hefty price tag. Investing in a
        car insurance policy is one of the ways you can protect your vehicle
        financially. This is what ClaimSafe is all about.
      </p>
    </div>
  </div>

  <!-- What is car insaurence -->
  <div class="bg-white max-w-full my-8 p-4">
    <h2 class="font-bold text-center text-xl p-4 md:text-3xl max-md:p-0">
      What is Car Insurance
    </h2>
    <div
      class="flex max-md:flex-col justify-around items-center p-4 gap-[10%]">
      <img
        src="./assets/what.webp"
        alt=""
        class="w-3/4 max-sm:h-36 h-44 object-fit relative right-6 md:hidden relative left-3" />
      <p class="p-4 text-sm font-sans text-wrap sm:text-base text-justify">
        Any damage to your car can be stressful and may become a financial
        burden. A car insurance policy is specially designed to help you
        deal with such events with a calm mind and the assurance of a
        financial safety net. <br /><br />
        Make sure you have an active car insurance policy at all times of
        owning the vehicle to avoid legal troubles and keep your beloved car
        insured. It is also important to choose the right car insurance
        policy and appropriate add-ons depending on your requirements to
        adequately cover your car.
      </p>
      <img
        src="./assets/what.webp"
        alt=""
        class="w-3/4 max-sm:h-36 h-44 object-fit relative right-6 max-md:hidden" />
    </div>
  </div>

  <!-- Different types of car insaurence -->
  <div class="bg-gray-200 max-w-full my-8 p-4 md:p-8">
    <h2 class="font-bold text-center text-xl p-4 md:text-3xl">
      Different Types of Accidental Insaurence
    </h2>
    <div
      class="flex max-sm:flex-col justify-around items-center p-4 gap-[10%]">
      <img
        src="./assets/different_types.webp"
        alt=""
        class="w-1/4 xl:h-52 max-sm:h-36 h-40 object-fit relative left-6 max-md:hidden" />
      <div
        class="w-[90%] h-96 sm:h-64 p-4 mx-6 rounded-xl shadow-lg bg-gray-100 relative">
        <h3 class="font-bold py-4 md:text-2xl" id="card-heading">
          Comprehensive Car Insaurence
        </h3>
        <p class="leading-relaxed font-normal" id="card-text">
          This is the most extensive coverage, offering proper protection
          against third-party liabilities and damage to your vehicle caused
          by accidents, natural calamities, theft, and fire. It also
          includes hospitalisation costs for drivers and passengers, towing
          charges, and personal accident cover.
        </p>

        <!-- left and right buttons -->
        <button
          id="left-button"
          class="absolute left-0 top-1/2 rounded-full p-2 bg-white z-10 -translate-x-1/2 shadow-xl"
          onclick="leftSlide()">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="size-6">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15.75 19.5 8.25 12l7.5-7.5" />
          </svg>
        </button>
        <button
          id="right-button"
          class="absolute right-0 top-1/2 rounded-full p-2 bg-white z-10 translate-x-1/2 shadow-xl"
          onclick="rightSlide()">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="size-6">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="m8.25 4.5 7.5 7.5-7.5 7.5" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Key benefits table -->
  <div class="flex justify-center flex-col items-center m-8">
    <h2 class="text-xl font-bold md:text-3xl p-4 md:p-6">
      Key Benefits of ClaimSafe
    </h2>
    <div
      class="overflow-hidden rounded-2xl shadow-lg border border-r-gray-300">
      <table class="w-full border-collapse">
        <thead>
          <tr class="bg-purple-700 text-white">
            <th class="p-4 text-left border border-r-gray-300">
              SBI General Key Features
            </th>
            <th class="p-4 text-left border border-r-gray-300">Benefits</th>
          </tr>
        </thead>
        <tbody>
          <tr class="border-t border-gray-300">
            <td class="p-4 border border-r-gray-300">
              Car insurance premium
            </td>
            <td class="p-4">Starts at ₹2094[AD1] *</td>
          </tr>
          <tr class="border-t border-gray-300">
            <td class="p-4 border border-r-gray-300">Cashless garages</td>
            <td class="p-4">
              Over 7000 network garages<br />Basic + additional roadside
              assistance
            </td>
          </tr>
          <tr class="border-t border-gray-300">
            <td class="p-4 border border-r-gray-300">No claim bonus</td>
            <td class="p-4">Up to 50%</td>
          </tr>
          <tr class="border-t border-gray-300">
            <td class="p-4 border border-r-gray-300">Add-on covers</td>
            <td class="p-4">Approx 13 add-ons</td>
          </tr>
          <tr class="border-t border-gray-300">
            <td class="p-4 border">Third-party damage cover</td>
            <td class="p-4">
              Covers injury/death of any third party and damage to any
              third-party property
            </td>
          </tr>
          <tr class="border-t border-gray-300">
            <td class="p-4 border border-r-gray-300">
              Personal accident cover
            </td>
            <td class="p-4">
              ₹15 lakhs for individual owners<br />₹2 lakhs/passenger for
              occupants of the vehicle
            </td>
          </tr>
          <tr class="border-t border-gray-300">
            <td class="p-4 border border-r-gray-300">Own damage cover</td>
            <td class="p-4">
              Covers fire, explosion, accidental damage, natural calamity
            </td>
          </tr>

          <tr class="border-t border-gray-300">
            <td class="p-4 border border-r-gray-300">
              Claim settlement ratio
            </td>
            <td class="p-4 border border-r-gray-50">98% for FY 2022-23</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- what covered inside insaurence -->
  <div class="w-full bg-gray-200 p-4">
    <h3 class="text-xl md:text-3xl font-black text-center p-4 md:py-8">
      Whats covered inside the ClaimSafe Policy
    </h3>
    <!-- for small screen -->

    <div
      class="w-[90%] min-h-96 sm:h-64 mx-6 rounded-lg shadow-md bg-gray-100 md:hidden">
      <div
        class="relative flex flex-col justify-center items-center gap-2 p-4">
        <img
          src="./assets/accident_image.webp"
          class="object-cover pt-1 h-16 w-16"
          alt=""
          id="card-image" />
        <h3 class="font-bold py-4 md:text-2xl" id="card-heading2">
          Damage/Loss
        </h3>
        <p
          class="leading-relaxed font-normal h-40 text-justify"
          id="card-text2">
          'Damage Loss coverage protects your vehicle against damages from
          accidents, theft, fire, natural calamities, and other unforeseen
          events. It covers the cost of repairs or replacement.',
        </p>

        <!-- left and right buttons -->
        <button
          id="left-button-2"
          class="absolute left-0 top-1/2 rounded-full p-2 bg-white z-10 -translate-x-1/2 shadow-xl"
          onclick="leftSlide2()">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="size-6">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15.75 19.5 8.25 12l7.5-7.5" />
          </svg>
        </button>
        <button
          id="right-button-2"
          class="absolute right-0 top-1/2 rounded-full p-2 bg-white z-10 translate-x-1/2 shadow-xl"
          onclick="rightSlide2()">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="size-6">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="m8.25 4.5 7.5 7.5-7.5 7.5" />
          </svg>
        </button>
      </div>
    </div>

    <!-- for larger screen -->
    <div
      class="grid grid-cols-3 grid-rows-2 w-full px-8 max-md:hidden gap-4">
      <div
        class="width-[30%] h-64 bg-white flex flex-col p-4 gap-2 rounded-md shadow-xl">
        <img
          src="./assets/personal_loss.webp"
          alt=""
          class="object-cover h-12 w-12" />
        <h4 class="font-bold text-md">Personal Accident Cover</h4>
        <p class="text-sm mt-4">
          Personal Accident Cover provides financial protection to the
          vehicle owner/driver in case of accidental death or disability.
          This coverage ensures compensation for unfortunate events while
          driving.
        </p>
      </div>

      <div
        class="width-[30%] h-64 bg-white flex flex-col p-4 gap-2 rounded-md shadow-xl">
        <img
          src="./assets/third_party.webp"
          alt=""
          class="object-cover h-12 w-12" />
        <h4 class="font-bold text-md">Third Party Liabilities</h4>
        <p class="text-sm mt-4">
          Third Party Liability coverage protects you against legal and
          financial liabilities arising from injury, death, or property
          damage caused to third parties by your insured vehicle.
        </p>
      </div>

      <div
        class="width-[30%] h-64 bg-white flex flex-col p-4 gap-2 rounded-md shadow-xl">
        <img
          src="./assets/accident_image.webp"
          alt=""
          class="object-cover h-12 w-12" />
        <h4 class="font-bold text-md">Damage/Loss</h4>
        <p class="text-sm mt-4">
          Damage Loss coverage protects your vehicle against damages from
          accidents, theft, fire, natural calamities, and other unforeseen
          events. It covers the cost of repairs or replacement.
        </p>
      </div>

      <div
        class="width-[30%] h-64 bg-white flex flex-col p-4 gap-2 rounded-md shadow-xl">
        <img
          src="./assets/no_claim.webp"
          alt=""
          class="object-cover h-12 w-12" />
        <h4 class="font-bold text-md">No Claim Bonus</h4>
        <p class="text-sm mt-4">
          No Claim Bonus is a reward in the form of discount on premium for
          not making any claims during the policy period. The discount
          increases progressively for each claim-free year.
        </p>
      </div>

      <div
        class="width-[30%] h-64 bg-white flex flex-col p-4 gap-2 rounded-md shadow-xl">
        <img
          src="./assets/raodside_assistance.webp"
          alt=""
          class="object-cover h-12 w-12" />
        <h4 class="font-bold text-md">Roadside Assistance</h4>
        <p class="text-sm mt-4">
          Roadside Assistance provides 24/7 emergency services including
          towing, battery jump-start, flat tire change, fuel delivery, and
          mechanical assistance when your vehicle breaks down.
        </p>
      </div>
    </div>
  </div>

  <!-- How to buy online-->

  <div class="w-full p-4" id="procedure">
    <h2 class="text-xl md:text-3xl text-center p-4 font-bold">
      How To Register Yourself Online
    </h2>
    <p class="text-center text-lg">Just follow these 4 rasy steps to get you started</p>
    <!-- father fiv -->
    <div class="w-full h-80 flex justify-center gap-4 p-4 mx-auto " id="parentSlide">

      <button onclick="buttons()"
        class=" h-92 max-w-1/4 border bg-purple-500 rounded-full flex items-center justify-center relative shadow-lg" id="slide1">
        <p class="absolute text-7xl text-white font-bold">1</p>
        <div class="self-end rounded-full h-20 w-16 text-center bg-white">
          <img
            src="./assets/steps1.webp"
            class="object-cover object-fit p-1"
            alt="" />
        </div>
      </button>

      <button onclick="buttons()"
        class=" h-92 max-w-1/4 border bg-purple-500 rounded-full flex items-center justify-center relative shadow-lg" id="slide2">
        <p class="absolute text-7xl text-white font-bold">2</p>
        <div class="self-end rounded-full h-20 w-16 text-center bg-white">
          <img
            src="./assets/steps2.webp"
            class="object-cover object-fit p-1"
            alt="" />
        </div>
      </button>

      <button onclick="buttons()"
        class=" h-92 max-w-1/4 border bg-purple-500 rounded-full flex items-center justify-center relative shadow-lg" id="slide3">
        <p class="absolute text-7xl text-white font-bold">3</p>
        <div class="self-end rounded-full h-20 w-16 text- bg-white">
          <img
            src="./assets/steps3.webp"
            class="object-cover object-fit p-1"
            alt="" />
        </div>
      </button>

      <button onclick="buttons()"
        class=" h-92 max-w-1/4 border bg-purple-500 rounded-full flex items-center justify-center relative shadow-lg" id="slide4">
        <p class="absolute text-7xl text-white font-bold">4</p>
        <div class="self-end rounded-full h-20 w-16 text-center bg-white">
          <img
            src="./assets/steps4.webp"
            class="object-cover object-fit p-1"
            alt="" />
        </div>
      </button>

    </div>
  </div>

  <div class="w-full bg-gray-200 p-4 pb-16" id="faq">
    <h1 class="text-xl md:text-3xl text-center font-bold p-4 md:pt-8 ">FAQs</h1>
    <!-- Questions -->
    <!-- q1 -->
    <div class="w-[95%] mx-auto flex justify-between items-center cursor-pointer p-2 " onclick="toggleAnswer(this)">

      <p class="text-md md:text-xl font-semibold ">What does accident insurance typically cover?</p>
      <button id="toggle-btn" class="bg-purple-500  p-1 rounded-full min-w-8 min-h-8 flex justify-center items-center text-white font-bold ">+</button>
    </div>
    <div class="w-[95%] mx-auto flex justify-between items-center cursor-pointer hidden">
      <p class="text-md md:text-xl font-normal answer p-2">Accident insurance typically covers medical expenses, hospital stays, emergency treatment, rehabilitation costs, and may include disability benefits or death benefits resulting from accidental injuries.</p>
    </div>

    <div class="w-[95%] mx-auto flex justify-between items-center cursor-pointer p-2" onclick="toggleAnswer(this)">
      <p class="text-md md:text-xl font-semibold">How quickly can I file a claim after an accident?</p>
      <button id="toggle-btn" class="bg-purple-500 p-1 rounded-full min-w-8 min-h-8 flex justify-center items-center text-white font-bold">+</button>
    </div>
    <div class="w-[95%] mx-auto flex justify-between items-center cursor-pointer hidden">
      <p class="text-md md:text-xl font-normal answer p-2">You can file a claim immediately after an accident. It's recommended to file within 24-48 hours of the incident to ensure prompt processing and avoid any potential issues with the claim.</p>
    </div>

    <div class="w-[95%] mx-auto flex justify-between items-center cursor-pointer p-2" onclick="toggleAnswer(this)">
      <p class="text-md md:text-xl font-semibold">Is pre-existing medical condition covered under accident insurance?</p>
      <button id="toggle-btn" class="bg-purple-500 p-1 rounded-full min-w-8 min-h-8 flex justify-center items-center text-white font-bold">+</button>
    </div>
    <div class="w-[95%] mx-auto flex justify-between items-center cursor-pointer hidden">
      <p class="text-md md:text-xl font-normal answer p-2">Generally, pre-existing conditions are not covered under accident insurance. The policy only covers injuries that directly result from new accidents occurring after the policy start date.</p>
    </div>

    <div class="w-[95%] mx-auto flex justify-between items-center cursor-pointer p-2" onclick="toggleAnswer(this)">
      <p class="text-md md:text-xl font-semibold">What documents are required for accident insurance claim?</p>
      <button id="toggle-btn" class="bg-purple-500 p-1 rounded-full min-w-8 min-h-8 flex justify-center items-center text-white font-bold">+</button>
    </div>
    <div class="w-[95%] mx-auto flex justify-between items-center cursor-pointer hidden">
      <p class="text-md md:text-xl font-normal answer p-2">Required documents typically include accident report, medical records, bills and receipts, identity proof, policy documents, and any relevant police reports or witness statements.</p>
    </div>

    <div class="w-[95%] mx-auto flex justify-between items-center cursor-pointer p-2" onclick="toggleAnswer(this)">
      <p class="text-md md:text-xl font-semibold">Can I have multiple accident insurance policies?</p>
      <button id="toggle-btn" class="bg-purple-500 p-1 rounded-full min-w-8 min-h-8 flex justify-center items-center text-white font-bold">+</button>
    </div>
    <div class="w-[95%] mx-auto flex justify-between items-center cursor-pointer hidden">
      <p class="text-md md:text-xl font-normal answer p-2">Yes, you can have multiple accident insurance policies. However, you must disclose all existing policies when applying for a new one. Each policy will pay according to its terms regardless of other coverage.</p>
    </div>
  </div>

</main>

<script src="./index.js"></script>
</body>

</html>