<?php
///**
// * Array of chemical element abbreviations.
// *
// * @return array
// */
//function imrs_abbr_elements() {
//  return [
//    'Ar' => [
//      'argon',
//      "A noble gas, non-reactive, but toxic in high concentrations. About 1% of Earth’s atmo, 2% of Mars’. Used in welding."
//    ],
//    'C' => [
//      'carbon',
//      "One of the most useful and abundant of all elements. Definitive element of organic molecules. Found in wood, plastic, steel, diamond, nanotubes and many other materials."
//    ],
//    'H' => [
//      'hydrogen',
//      "The lightest and most abundant element in the universe. The main element in stars, commonly found in water and hydrocarbons."
//    ],
//    'N' => [
//      'nitrogen',
//      "An essential element for life, found in all living things. Comprises about 80% of Earth’s atmosphere."
//    ],
//    'O' => [
//      'oxygen',
//      "An essential element for life, and extremely abundant. Most often found in water, air as (O2), and in rocks as metal oxides."
//    ],
//    'Pu' => [
//      'plutonium',
//      "A radioactive metal often used as power source in mobile robots and spacecraft."
//    ],
//  ];
//}

///**
// * Generate HTML table of elements.
// *
// * @return string
// */
//function imrs_abbr_element_table() {
//  $elements = imrs_abbr_elements();
//  $header = [
//    'Symbol',
//    'Element',
//    'Description'
//  ];
//  $rows = [];
//  foreach ($elements as $abbr => $element) {
//    $rows[] = [
//      [
//        'data' => $abbr,
//        'class' => 'abbr'
//      ],
//      $element[0],
//      $element[1]
//    ];
//  }
//  return theme('table', [
//    'header' => $header,
//    'rows' => $rows,
//    'sticky' => FALSE
//  ]);
//}

///**
// * Generate HTML table of compounds.
// *
// * @return string
// */
//function imrs_abbr_compound_table() {
//  $compounds = imrs_abbr_compounds();
//  $header = [
//    'Symbol',
//    'Compound',
//    'Description'
//  ];
//  $rows = [];
//  foreach ($compounds as $abbr => $compound) {
//    $rows[] = [
//      [
//        'data' => $abbr,
//        'class' => 'abbr'
//      ],
//      $compound[0],
//      $compound[1]
//    ];
//  }
//  return theme('table', [
//    'header' => $header,
//    'rows' => $rows,
//    'sticky' => FALSE
//  ]);
//}

///**
// * Generate HTML table of compounds.
// *
// * @return string
// */
//function imrs_abbr_compound_table() {
//  $compounds = imrs_abbr_compounds();
//  $header = [
//    'Symbol',
//    'Compound',
//    'Description'
//  ];
//  $rows = [];
//  foreach ($compounds as $abbr => $compound) {
//    $rows[] = [
//      [
//        'data' => $abbr,
//        'class' => 'abbr'
//      ],
//      $compound[0],
//      $compound[1]
//    ];
//  }
//  return theme('table', [
//    'header' => $header,
//    'rows' => $rows,
//    'sticky' => FALSE
//  ]);
//}

///**
// * Generate HTML table of acronyms.
// *
// * @return string
// */
//function imrs_abbr_acronym_table() {
//  $acronyms = imrs_abbr_acronyms();
//  $header = [
//    'Symbol',
//    'Acronym',
//    'Description'
//  ];
//  $rows = [];
//  foreach ($acronyms as $abbr => $acronym) {
//    $rows[] = [
//      [
//        'data' => $abbr,
//        'class' => 'abbr'
//      ],
//      $acronym[0],
//      $acronym[1]
//    ];
//  }
//  return theme('table', [
//    'header' => $header,
//    'rows' => $rows,
//    'sticky' => FALSE
//  ]);
//}

///**
// * Array of references.
// *
// * @return array
// */
//function imrs_abbr_references() {
//  return [
//    'ACIKMESE' => [
//      'ref' => "B. Acikmese, J. Casoliva, and J. M. Carson III, “G-FOLD: A Real-Time Implementable Fuel Optimal Large Divert Guidance Algorithm for Planetary Pinpoint Landing,” Concepts and Approaches for Mars Exploration, 2012.",
//      'pdf' => 'Acikmese, Casoliva, III - 2012 - G-FOLD A Real-Time Implementable Fuel Optimal Large Divert Guidance Algorithm for Planetary Pinpoint La.pdf',
//      'links' => ['http://www.researchgate.net/publication/258676350_G-FOLD_A_Real-Time_Implementable_Fuel_Optimal_Large_Divert_Guidance_Algorithm_for_Planetary_Pinpoint_Landing',],
//    ],
//    'BONIN' => [
//      'ref' => "G. Bonin, “Reaching Mars for Less: The Reference Mission Design of the MarsDrive Consortium,” 25th International Space Development Conference, Los Angeles, California, May 2006.",
//      'pdf' => 'Bonin - 2006 - Reaching Mars for Less.pdf',
//      'links' => [
//        'http://www.mendeley.com/research/reaching-mars-less-reference-mission-design-marsdrive-consortium/',
//        'http://www.researchgate.net/publication/228802804_Reaching_Mars_for_less_The_reference_mission_design_of_the_MarsDrive_Consortium',
//      ],
//    ],
//    'CAMPBELL' => [
//      'ref' => "P. D. Campbell, “Internal Atmospheric Pressure and Composition for Planet Surface Habitats and Extravehicular Mobility Units,” Lockheed Engineering and Sciences Company, Contract NAS9-17900, Job Order K1-ETB, Report No. JSC-25003, for NASA Man-Systems Division, 1991.",
//      'pdf' => 'Campbell - 1991 - Internal Atmospheric Pressure and Composition for Planet Surface Habitats and Extravehicular Mobility Units.pdf',
//      'links' => ['http://ares.jsc.nasa.gov/humanexplore/exploration/exlibrary/docs/eic017.html',],
//    ],
//    'COOPER' => [
//      'ref' => "C. Cooper, W. Hofstetter, J. A. Hoffman, and E. F. Crawley, “Assessment of architectural options for surface power generation and energy storage on human Mars missions,” Acta Astronaut., vol. 66, no. 7–8, pp. 1106–1112, Apr 2010.",
//      'pdf' => 'Cooper - 2009 - Assessment of architectural options for surface power generation and energy storage on human Mars missions.pdf',
//      'links' => [
//        'http://www.mendeley.com/research/assessment-architectural-options-surface-power-generation-energy-storage-human-mars-missions/',
//        'https://www.researchgate.net/publication/223477541_Assessment_of_architectural_options_for_surface_power_generation_and_energy_storage_on_human_Mars_missions',
//      ],
//    ],
//    'DELAUNE' => [
//      'ref' => "J. Delaune, G. Le Besnerais, M. Sanfourche, T. Voirin, C. Bourdarias, and J. Farges, “Optical Terrain Navigation for Pinpoint Landing: Image Scale and Position-Guided Landmark Matching,” Proceedings of the 35th Annual Guidance and Control Conference, 2012.",
//      'pdf' => 'Delaune et al. - 2012 - Optical Terrain Navigation for Pinpoint Landing Image Scale and Position-Guided Landmark Matching.pdf',
//      'links' => ['http://www.mendeley.com/research/optical-terrain-navigation-pinpoint-landing-image-scale-positionguided-landmark-matching/',],
//    ],
//    'DRAKE' => [
//      'ref' => 'B. G. Drake, “Reference Mission Version 3.0 Addendum to the Human Exploration of Mars: The Reference Mission of the NASA Mars Exploration Study Team,” no. June, 1998.',
//      'pdf' => 'NASA 1998-06 Reference Mission Version 3.0 Addendum.pdf',
//      'links' => [
//        'https://www.researchgate.net/publication/260970073_Reference_Mission_Version_3.0_Addendum_to_the_Human_Exploration_of_Mars_The_Reference_Mission_of_the_NASA_Mars_Exploration_Study_Team',
//        'http://ares.jsc.nasa.gov/HumanExplore/Exploration/EXLibrary/docs/MarsRef/addendum/index.htm',
//      ],
//    ],
//    'DRAKE2' => ['ref' => "B. G. Drake, &ldquo;Human exploration of Mars, Design Reference Architecture 5.0 & rdquo;, 2010, p. 100., Mars Architecture Steering Group, NASA / SP–2009–566.",],
//    'DUFFIELD' => [
//      'ref' => "B. E. Duffield, “Advanced Life Support Requirements Document,” JSC-38571, Revision C, National Aeronautics and Space Administration, Lyndon B. Johnson Space Center, Houston, Texas, 2003.",
//      'pdf' => 'Duffield - 2003 - Advanced Life Support Requirements Document.pdf',
//    ],
//    'ETHRIDGE' => [
//      'ref' => "E. C. Ethridge and W. F. Kaukler, “Microwave Extraction of Volatiles for Mars Science and ISRU,” AIAA Aerospace Sciences Meeting, 2012.",
//      'pdf' => 'Ethridge - Microwave Extraction of Volatiles for Mars Science and ISRU.pdf',
//      'links' => ['https://www.researchgate.net/publication/258676377_Microwave_Extraction_of_Volatiles_for_Mars_Science_and_ISRU',],
//    ],
//    'FOGG' => [
//      'ref' => "M. J. Fogg, “The Utility of Geothermal Energy on Mars,” J. Br. Interplanet. Soc., vol. 49, pp. 403–422, 1996.",
//      'pdf' => 'Fogg - 1996 - The Utility of Geothermal Energy on Mars.pdf',
//      'links' => [
//        'https://www.academia.edu/4156745/The_Utility_of_Geothermal_Energy_on_Mars',
//        'https://www.researchgate.net/publication/260843953_The_utility_of_geothermal_energy_on_Mars',
//      ],
//    ],
//    'GAGE' => [
//      'ref' => "D. Gage, “Mars Base First: A Program-level Optimization for Human Mars Exploration”, J. Cosmol., vol 12, pp. 3904-3911, 2010.",
//      'pdf' => 'Gage - 2010 - Mars Base First - A Program-level Optimization for Human Mars Exploration.pdf',
//      'links' => ['http://journalofcosmology.com/Mars103.html',],
//    ],
//    'GROVER' => [
//      'ref' => "M. R. Grover, M. O. Hilstad, L. M. Elias, K. G. C. M. A. Schneider, C. S. Hoffman, S. Adan-Plaza, and A. P. Bruckner, “Extraction of Atmospheric Water on Mars in Support of the Mars Reference Mission,” MAR 98-062, Proceedings of the Founding Convention of the Mars Society: Part II, ed. R. M. Zubrin and M. Zubrin, Boulder, CO, pp. 659-679, August 13-16, 1998.",
//      'pdf' => 'Grover - 1998 - Extraction of Atmospheric Water on Mars.pdf',
//      'links' => [
//        'http://www.mendeley.com/research/extraction-atmospheric-water-mars-support-mars-reference-mission/',
//        'https://www.researchgate.net/publication/234462002_Extraction_of_Atmospheric_Water_on_Mars_for_the_Mars_Reference_Mission',
//      ],
//    ],
//    'HANFORD' => [
//      'ref' => "A. J. Hanford, “Advanced Life Support Baseline Values and Assumptions Document,” NASA Johnson Space Centre, 2004.",
//      'pdf' => 'Hanford - 2006 - Advanced Life Support Baseline Values and Assumptions Document.pdf',
//      'links' => [
//        'http://www.mendeley.com/catalog/advanced-life-support-baseline-values-assumptions-document/',
//        'https://www.researchgate.net/publication/27238000_Advanced_Life_Support--Baseline_Values_and_Assumptions_Document',
//      ],
//    ],
//    'HOFFMAN' => [
//      'ref' => "S. J. Hoffman and D. I. Kaplan, “Human Exploration of Mars: The Reference Mission of the NASA Mars Exploration Study Team,” NASA Spec. Publ., vol. 6107, no. July, pp. 98–036, 1998.",
//      'pdf' => 'NASA 1997-07 The Reference Mission of the NASA Mars Exploration Study Team.pdf',
//      'links' => [
//        'http://en.wikipedia.org/wiki/NASA_Design_Reference_Mission_3.0',
//        'http://ares.jsc.nasa.gov/HumanExplore/Exploration/EXLibrary/docs/MarsRef/contents.htm',
//        'http://www.astronautix.com/craft/dession3.htm',
//      ],
//    ],
//    'INTERBARTOLO' => [
//      'ref' => "M. A. Interbartolo III, G. B. Sanders, L. Oryshchyn, K. Lee, H. Vaccaro, E. Santiago-Maldonado, and Anthony C. Muscatello, “Prototype Development of an Integrated Mars Atmosphere and Soil-Processing System,” J. Aerosp. Eng., vol 26, SPECIAL ISSUE: In Situ Resource Utilization, pp. 57–66, 2013.",
//      'links' => [
//        'http://www.mendeley.com/catalog/prototype-development-integrated-mars-atmosphere-soilprocessing-system/',
//        'http://ascelibrary.org/doi/abs/10.1061/(ASCE)AS.1943-5525.0000214',
//        'http://www.slideshare.net/minterbartolo/prototype-development-of-an-integrated-mars-atmosphere-and-soil-processing-system'
//      ]
//    ],
//    'SMAC' => [
//      'ref' => "JSC 20584, “Spacecraft Maximum Allowable Concentrations For Airborne Contaminants,” Toxicology Group, Medical Operations Branch, Medical Sciences Division, Space and Life Sciences Directorate, NASA, Johnson Space Center, June 1999.",
//      'pdf' => 'JSC 20584 - 1999 - Spacecraft Maximum Allowable Concentrations For Airborne Contaminants.pdf',
//      'links' => ['http://www.nasa.gov/centers/johnson/slsd/about/divisions/hefd/facilities/toxicology-exposure.html',],
//    ],
//    'LARSON' => [
//      'ref' => "W. J. Larson and L. K. Pranke, “Human Spaceflight: Mission Analysis and Design (Space Technology Series),” McGraw-Hill, 1999.",
//      'links' => [
//        'http://www.amazon.com/Human-Spaceflight-Mission-Analysis-Technology/dp/007236811X',
//        'https://www.mcgraw-hill.co.uk/html/0077230280.html',
//      ],
//    ],
//    'KARCZ' => [
//      'ref' => "J. S. Karcz, S. M. Davis, M. J. Aftosmis, G. A. Allen, N. M. Bakhtian, A. A. Dyakonov, K. T. Edquist, B. J. Glass, A. A. Gonzales, J. L. Heldmann, L. G. Lemke, M. M. Marinova, C. P. Mckay, C. R. Stoker, P. D. Wooster, and K. A. Zarchi, “Red Dragon: Low-Cost Access to the Surface of Mars Using Commercial Capabilities,” Concepts and Approaches for Mars Exploration, 2012.",
//      'pdf' => 'Karcz - 2012 - Red Dragon - Low Cost Access to the Surface of Mars Using Commercial Capabilities.pdf',
//      'links' => ['https://www.researchgate.net/publication/258676367_Red_Dragon_Low-Cost_Access_to_the_Surface_of_Mars_Using_Commercial_Capabilities',]
//    ],
//    'KOZICKI' => [
//      'ref' => "J. Kozicki and J. Kozicka, “Human friendly architectural design for a small Martian base,” Adv. Sp. Res., vol. 48, no. 12, Dec 2011.",
//      'pdf' => 'Kozicki - 2011 - Human friendly architectural design for a small Martian base.pdf',
//      'links' => [
//        'http://www.mendeley.com/research/human-friendly-architectural-design-small-martian-base/',
//        'https://www.researchgate.net/publication/251546729_Human_friendly_architectural_design_for_a_small_Martian_base',
//        'http://www.sciencedirect.com/science/article/pii/S0273117711006466',
//      ],
//    ],
//    'LANSDORP' => [
//      'ref' => "B. Lansdorp, A. A. Wielders, “Mars One Communications System,” http://www.mars-one.com/technology/communications-system (Retrieved 2014-05-26).",
//      'pdf' => 'Lansdorp - 2013 - Mars One Communications System.pdf',
//      'links' => ['http://www.mars-one.com/technology/communications-system',]
//    ],
//    'RHINEHART' => [
//      'ref' => "R. Rhinehart, “Soylent - Free Your Body,” https://campaign.soylent.me/soylent-free-your-body (Retrieved 2013-10-15)",
//      'pdf' => 'Soylent.pdf',
//      'links' => [
//        'http://www.soylent.me/',
//        'http://crowdtilt.soylent.me/',
//      ],
//    ],
//    'STOKER' => [
//      'ref' => "C. R. Stoker, A. Davila, S. Davis, B. Glass, A. Gonzales, J. Heldmann, J. Karcz, L. Lemke, and G. Sanders, “Ice Dragon: A Mission to Address Science and Human Exploration Objectives on Mars,” Concepts and Approaches for Mars Exploration, 2012.",
//      'pdf' => 'Stoker - 2012 - Ice Dragon - A Mission to Address Science and Human Exploration Objectives on Mars.pdf',
//      'links' => [
//        'http://www.mendeley.com/research/ice-dragon-mission-address-science-human-exploration-objectives-mars/',
//        'https://www.researchgate.net/publication/258676196_Ice_Dragon_A_Mission_to_Address_Science_and_Human_Exploration_Objectives_on_Mars',
//        'http://adsabs.harvard.edu/abs/2012LPICo1679.4176S',
//        'http://nix.nasa.gov/search.jsp?R=20120016811&qs=N%3D4294966753%2B4294941275%2B4294965619',
//      ]
//    ],
//    'WAMELINK' => [
//      'ref' => "G. W. W. Wamelink, &ldquo;Growing plants on Mars: Wageningen UR goes extraterrestrial&rdquo;, Wageningen UR, https://www.wageningenur.nl/en/show/Growing-plants-on-Mars-Wageningen-UR-goes-extraterrestrial.htm, 2013, Retrieved 2014-06-09",
//      'pdf' => 'Wamelink - Growing plants on Mars.pdf',
//      'links' => ['https://www.wageningenur.nl/en/show/Growing-plants-on-Mars-Wageningen-UR-goes-extraterrestrial.htm',]
//    ],
//    'WIELDERS' => [
//      'ref' => "A. Wielders, B. Lansdorp, S. Flinkenflögel, B. Versteeg, N. Kraft, E. Vaandrager, M. Wagensveld, A. Dogra, B. Casagrande and N. Aziz, “Mars One: Creating a human settlement on Mars,” European Planetary Science Congress 2013, vol. 8, 2013.",
//      'pdf' => 'Wielders - 2013 - Mars One - Creating a Human Settlement on Mars.pdf',
//    ],
//    'WILLSON' => [
//      'ref' => "D. Willson and J. D. A. Clarke, “A Practical Architecture for Exploration-Focused Manned Mars Missions Using Chemical Propulsion, Solar Power Generation and In-Situ Resource Utilisation,” J. Br. Interplanet. Soc., vol. 58, pp. 181–196, 2005.",
//      'pdf' => 'Willson - 2006 - A Practical Architecture for Exploration-Focused Manned Mars Missions.pdf',
//      'links' => [
//        'http://www.nssa.com.au/ocs/viewabstract.php?id=119&cf=7',
//        'http://www.astronautix.com/craft/marsoz.htm',
//        'http://marssociety.org.au/project/mars-oz',
//      ]
//    ],
//    'ZUBRIN' => [
//      'ref' => "R. M. Zubrin, D. A. Baker, and O. Gwynne, “Mars direct - A simple, robust, and cost effective architecture for the Space Exploration Initiative,” 29th Aerosp. Sci. Meet. AIAA, 1991.",
//      'pdf' => 'Zubrin - 1991 - Mars Direct - A Simple, Robust, and Cost Effective Architecture for the Space Exploration Initiative.pdf',
//      'links' => [
//        'https://www.researchgate.net/publication/4702054_Mars_direct_-_A_simple_robust_and_cost_effective_architecture_for_the_Space_Exploration_Initiative',
//        'http://en.wikipedia.org/wiki/Mars_Direct',
//        'http://www.astronautix.com/craft/marirect.htm',
//      ]
//    ],
//  ];
//}

/**
 * Generate the HTML for the references page.
 *
 * @return string
 */
function imrs_abbr_ref_list() {
  $references = imrs_abbr_references();
  $html = '';
  $i = 1;
  foreach ($references as $info) {
    $html .= "<p id='ref{$i}'>[$i] {$info['ref']}";
    if (isset($info['pdf'])) {
      $html .= " <a href='/sites/default/media/doc/{$info['pdf']}' target='_blank'><img src='/sites/all/themes/custom/imrs/images/icons/pdf-icon-16x16.png' title='PDF'></a>";
    }
    if (isset($info['links'])) {
      foreach ($info['links'] as $link) {
        if (strpos($link, 'mendeley') !== FALSE) {
          $icon = 'mendeley';
          $title = 'Mendeley';
        }
        elseif (strpos($link, 'academia.edu') !== FALSE) {
          $icon = 'academia';
          $title = 'Academia.edu';
        }
        elseif (strpos($link, 'researchgate') !== FALSE) {
          $icon = 'researchgate';
          $title = 'ResearchGate';
        }
        elseif (strpos($link, 'wikipedia') !== FALSE) {
          $icon = 'wikipedia';
          $title = 'Wikipedia';
        }
        elseif (strpos($link, 'amazon') !== FALSE) {
          $icon = 'amazon';
          $title = 'Amazon';
        }
        else {
          $icon = 'external-link';
          $title = 'External link';
        }
        $html .= " <a href='$link' target='_blank'><img src='/sites/all/themes/custom/imrs/images/icons/$icon-icon-16x16.png' title='$title'></a>";
      }
    }
    $html .= "</p>";
    $i++;
  }
  return $html;
}

/**
 * Generate the HTML with tables for chemical elements and compounds, and
 * acronyms.
 *
 * @return string
 */
function imrs_abbr_table() {
  return "
    <p>Numerous acronyms appear in this document; all are listed here for easy
    reference. They are drawn from several sources:
    <ul>
      <li>Space</li>
      <li>Military</li>
      <li>Mining</li>
      <li>Newly invented in this document </li>
    </ul>
    To mitigate acronym overload and reduce time spent flicking back and forth to this page, acronyms are expanded on first usage.</p>
    <p>&nbsp;</p>
    <h2>Chemical elements</h2>" . imrs_abbr_element_table() . "
    <p>&nbsp;</p>
    <h2>Chemical compounds</h2>" . imrs_abbr_compound_table() . "
    <p>&nbsp;</p>
    <h2>Acronyms</h2>" . imrs_abbr_acronym_table();
}
