<router-link tag="h3" :to="{name: 'nova-installer'}" class="cursor-pointer flex items-center font-normal dim text-white mb-6 text-base no-underline">
    <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="#B3C1D1" d="M0 2C0 .9.9 0 2 0h16a2 2 0 0 1 2 2v2H0V2zm1 3h18v13a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V5zm6 2v2h6V7H7z"/></svg>
    <span class="sidebar-label">
        Package Installer
    </span>
</router-link>
<nova-installer-navigation ref="nova-installer-navigation"></nova-installer-navigation>