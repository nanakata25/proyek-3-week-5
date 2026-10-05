@props(['id'])
<svg viewBox="0 0 240 168" fill="none" aria-hidden="true" class="product-art">
    <ellipse cx="120" cy="142" rx="64" ry="9" fill="#123A40" opacity=".08"/>
    @if($id == 1)
        <g transform="rotate(-11 120 84)"><rect x="67" y="21" width="106" height="123" rx="6" fill="#123A40"/><path d="M78 23v120" stroke="#43878A" stroke-width="3"/><rect x="94" y="51" width="58" height="49" rx="2" fill="#FCF8EB"/><path d="M106 65h35M106 74h35M106 84h23" stroke="#96A29A" stroke-width="2"/><path d="M89 131h70" stroke="#43878A" stroke-width="2"/></g>
    @elseif($id == 2)
        <g transform="rotate(28 120 84)"><rect x="107" y="29" width="21" height="94" rx="7" fill="#235C74"/><rect x="107" y="27" width="21" height="31" rx="6" fill="#143D51"/><path d="M127 31h7v37" stroke="#C3CDC8" stroke-width="4" stroke-linecap="round"/><path d="m109 123 9 20 9-20" fill="#C0CDC9"/><path d="m116 139 2 6 2-6" fill="#163B43"/><path d="M112 66v48" stroke="#5695A2" stroke-width="3" stroke-linecap="round"/></g>
    @elseif($id == 3)
        <g transform="rotate(-18 120 84)"><rect x="33" y="67" width="173" height="40" rx="5" fill="#D6A757"/><rect x="37" y="70" width="165" height="33" rx="3" stroke="#ECC782"/><path d="M46 69v17m12-17v9m12-9v17m12-17v9m12-9v17m12-17v9m12-9v17m12-17v9m12-9v17m12-17v9m12-9v17m12-17v9m12-9v17" stroke="#745128" stroke-width="2"/></g>
    @elseif($id == 4)
        <g transform="rotate(32 120 84)"><path d="M112 23h17v108h-17z" fill="#D4AD56"/><path d="M118 23h5v108h-5z" fill="#E9C879"/><path d="m112 131 8.5 20 8.5-20" fill="#DAC1A0"/><path d="m117 143 3.5 8 3.5-8" fill="#243940"/><rect x="112" y="23" width="17" height="14" rx="2" fill="#264E46"/></g>
    @else
        <g transform="rotate(-12 120 84)"><rect x="71" y="60" width="101" height="62" rx="8" fill="#DDB2A7"/><path d="M91 60h60v62H91z" fill="#FBF7EA"/><path d="M104 83h34M104 91h24" stroke="#82948A" stroke-width="3"/><path d="M73 111h97" stroke="#123A40" stroke-opacity=".08" stroke-width="3"/></g>
    @endif
</svg>
