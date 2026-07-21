<?php
/**
 * Nac News Portal - Database Seeder
 * Seeds initial data for the application
 */

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$name = getenv('DB_NAME');

$conn = new mysqli($host, $user, $pass, $name);
if ($conn->connect_error) {
    die("[!] Seed failed: " . $conn->connect_error . "\n");
}

$conn->set_charset('utf8mb4');

// Check if already seeded
$result = $conn->query("SELECT COUNT(*) as cnt FROM users");
$row = $result->fetch_assoc();
if ($row['cnt'] > 0) {
    echo "[*] Database already seeded, skipping.\n";
    $conn->close();
    exit(0);
}

echo "[*] Seeding users...\n";

// === USERS ===
$users = [
    // id=1: Admin (strong password - not guessable)
    [1, 'admin', 'admin@nac-news.com', password_hash('Xk9#mQ2$vL8@pR5!nW3zT7', PASSWORD_BCRYPT), 'Alexander Magnus', 'admin', 'Chief Administrator & System Architect of Nac News Portal. Managing digital news infrastructure since 2019.', null, 'admin-recovery@nac-secure.com', '+1-555-0100'],
    // id=2: Default reader account (given to players)
    [2, 'reader', 'reader@nac-news.com', password_hash('reader2024', PASSWORD_BCRYPT), 'Alex Reader', 'subscriber', 'Avid news reader and community member.', null, 'reader@gmail.com', null],
    // id=3: Reporter
    [3, 'michael_ross', 'michael@nac-news.com', password_hash('M1ch43l_R0ss!2024', PASSWORD_BCRYPT), 'Michael Ross', 'reporter', 'Senior Technology Correspondent covering AI, quantum computing, and emerging tech.', null, 'michael.ross@gmail.com', '+1-555-0103'],
    // id=4: Reporter
    [4, 'emma_chen', 'emma@nac-news.com', password_hash('3mmaC_S3cur3#2024', PASSWORD_BCRYPT), 'Emma Chen', 'reporter', 'Science & Health reporter specializing in biotech and climate research.', null, 'emma.chen@outlook.com', '+1-555-0104'],
    // id=5: Editor (Flag 4 - weak password: sarah2024!)
    [5, 'sarah_editor', 'sarah@nac-news.com', password_hash('sarah2024!', PASSWORD_BCRYPT), 'Sarah Williams', 'editor', 'Senior Content Editor. Responsible for editorial review and content curation. 8 years of digital journalism experience.', null, 'sarah.w@gmail.com', '+1-555-0105'],
    // id=6: Reporter
    [6, 'david_park', 'david@nac-news.com', password_hash('D4vid_P@rk_2024!', PASSWORD_BCRYPT), 'David Park', 'reporter', 'Business & Finance analyst covering global markets and cryptocurrency.', null, 'david.park@yahoo.com', '+1-555-0106'],
    // id=7: Reporter
    [7, 'lisa_johnson', 'lisa@nac-news.com', password_hash('L1s4_J0hns0n#24', PASSWORD_BCRYPT), 'Lisa Johnson', 'reporter', 'Culture & Arts correspondent. Covers archaeology, history, and cultural events worldwide.', null, 'lisa.j@gmail.com', '+1-555-0107'],
    // id=8: Reporter
    [8, 'james_wilson', 'james@nac-news.com', password_hash('J4m3s_W!ls0n_24', PASSWORD_BCRYPT), 'James Wilson', 'reporter', 'Opinion columnist and political analyst.', null, 'james.wilson@protonmail.com', '+1-555-0108'],
    // id=9: Subscriber
    [9, 'maria_garcia', 'maria@gmail.com', password_hash('M4r1a_G2024!', PASSWORD_BCRYPT), 'Maria Garcia', 'subscriber', 'Regular reader interested in science and technology.', null, null, null],
    // id=10: Subscriber
    [10, 'tom_baker', 'tom.baker@outlook.com', password_hash('T0m_B4k3r#2024', PASSWORD_BCRYPT), 'Tom Baker', 'subscriber', 'Tech enthusiast and frequent commenter.', null, null, null],
    // id=11: Subscriber
    [11, 'anna_white', 'anna.white@gmail.com', password_hash('4nn4_Wh1t3!24', PASSWORD_BCRYPT), 'Anna White', 'subscriber', null, null, null, null],
    // id=12: Subscriber
    [12, 'robert_lee', 'robert.lee@yahoo.com', password_hash('R0b3rt_L33#24', PASSWORD_BCRYPT), 'Robert Lee', 'subscriber', 'Business professional following market news.', null, null, null],
    // id=13: System Bot (Flag 3 - IDOR target, recovery_email is the flag)
    [13, 'sysbot', 'system@nac-news.com', password_hash('$ySb0t_1nt3rn4l_0nLy_2024!@#', PASSWORD_BCRYPT), 'System Bot', 'subscriber', 'Automated system account for scheduled tasks and content aggregation. Internal use only.', null, 'sysbot@internal.nac.local', null],
    // id=14: Another subscriber
    [14, 'jennifer_miles', 'jennifer@gmail.com', password_hash('J3nn_M1l3s!24', PASSWORD_BCRYPT), 'Jennifer Miles', 'subscriber', 'Freelance journalist and avid reader.', null, null, null],
    // id=15: Another editor
    [15, 'mark_thompson', 'mark@nac-news.com', password_hash('M4rk_Th0mp$0n_2024!X', PASSWORD_BCRYPT), 'Mark Thompson', 'editor', 'Deputy Editor. Manages weekend editions and special reports.', null, 'mark.t@gmail.com', '+1-555-0115'],
];

$stmt = $conn->prepare("INSERT INTO users (id, username, email, password, full_name, role, bio, avatar, recovery_email, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($users as $u) {
    $stmt->bind_param('isssssssss', $u[0], $u[1], $u[2], $u[3], $u[4], $u[5], $u[6], $u[7], $u[8], $u[9]);
    $stmt->execute();
}
$stmt->close();

echo "[*] Seeding categories...\n";

// === CATEGORIES ===
$categories = [
    [1, 'Technology', 'technology', 'Latest technology news, gadgets, AI, and digital innovation', 'fas fa-microchip'],
    [2, 'Science', 'science', 'Scientific discoveries, research breakthroughs, and space exploration', 'fas fa-flask'],
    [3, 'World', 'world', 'International news, diplomacy, and global events', 'fas fa-globe'],
    [4, 'Business', 'business', 'Markets, economy, startups, and corporate news', 'fas fa-chart-line'],
    [5, 'Culture', 'culture', 'Arts, history, archaeology, and cultural heritage', 'fas fa-landmark'],
    [6, 'Opinion', 'opinion', 'Expert analysis, editorials, and commentary', 'fas fa-comment-dots'],
    [7, 'Health', 'health', 'Medical research, public health, and wellness', 'fas fa-heartbeat'],
];

$stmt = $conn->prepare("INSERT INTO categories (id, name, slug, description, icon) VALUES (?, ?, ?, ?, ?)");
foreach ($categories as $c) {
    $stmt->bind_param('issss', $c[0], $c[1], $c[2], $c[3], $c[4]);
    $stmt->execute();
}
$stmt->close();

echo "[*] Seeding articles...\n";

// === ARTICLES ===
$articles = [
    // Featured article
    [
        'Revolutionary AI System Breaks New Ground in Climate Prediction',
        'revolutionary-ai-system-breaks-new-ground-climate-prediction',
        '<p>In a landmark achievement for artificial intelligence and environmental science, researchers at the Global Climate Research Institute (GCRI) have unveiled an AI system capable of predicting regional climate patterns with unprecedented accuracy up to 18 months in advance.</p>
<p>The system, dubbed "ClimateNet-7," utilizes a novel transformer architecture trained on over 40 years of satellite imagery, ocean temperature data, and atmospheric measurements from more than 12,000 monitoring stations worldwide. Unlike previous models that operated on coarse continental scales, ClimateNet-7 can generate predictions at the sub-regional level, covering areas as small as 50 square kilometers.</p>
<p>"This represents a paradigm shift in our ability to prepare for extreme weather events," said Dr. Helena Vasquez, lead researcher at GCRI. "Municipal governments and agricultural communities can now plan with far greater confidence for what lies ahead."</p>
<p>The AI model demonstrated a 94.7% accuracy rate during backtesting against historical data from 2010-2023, significantly outperforming the previous best of 78.3% achieved by conventional ensemble models. Most impressively, it correctly predicted the unusual drought patterns observed in Southeast Asia during late 2023, a phenomenon that surprised traditional forecasting methods.</p>
<p>The technology builds upon recent advances in attention mechanisms and physics-informed neural networks, which allow the model to learn not just statistical correlations but also fundamental atmospheric dynamics. The research team incorporated constraints from thermodynamic principles directly into the model\'s loss function, ensuring that predictions remain physically plausible.</p>
<p>Several national meteorological agencies have already expressed interest in integrating ClimateNet-7 into their forecasting pipelines. The UK Met Office and Japan Meteorological Agency are expected to begin pilot programs in Q2 2025.</p>
<p>The full research paper has been published in Nature Climate Change, with the model weights scheduled for open-source release in March 2025.</p>',
        'Researchers unveil ClimateNet-7, an AI system predicting regional climate with 94.7% accuracy up to 18 months ahead.',
        1, 3, 'published', null, 1, 15420, '2025-01-15 09:00:00'
    ],
    // Article 2
    [
        'Global Summit on Digital Privacy Concludes with Landmark Agreement',
        'global-summit-digital-privacy-landmark-agreement',
        '<p>Representatives from 87 nations concluded the three-day Global Digital Privacy Summit in Geneva with the signing of the International Digital Rights Framework (IDRF), the most comprehensive multilateral agreement on data privacy ever achieved.</p>
<p>The framework establishes baseline protections for citizens\' digital data across signatory nations, including mandatory data breach notification requirements, restrictions on cross-border data transfers without adequate protections, and the establishment of an international arbitration body for privacy disputes.</p>
<p>"Today marks the beginning of a new era in digital governance," said EU Commissioner for Digital Affairs, Dr. Martin Schreiber. "For the first time, we have global consensus on the fundamental rights of individuals in the digital space."</p>
<p>Key provisions of the IDRF include a requirement for explicit consent before collecting biometric data, a universal right to data portability, and restrictions on algorithmic decision-making in areas like employment, credit, and criminal justice without human oversight.</p>
<p>Notable holdouts from the agreement include the United States, Russia, and China, though all three nations participated as observers and have indicated willingness to engage in further negotiations. US Trade Representative Sarah Mitchell stated that while the Biden administration supports the framework\'s goals, concerns remain about potential impacts on American technology companies.</p>
<p>The agreement is expected to come into force in January 2026, giving signatory nations 18 months to implement necessary legislative changes.</p>',
        'The International Digital Rights Framework signed by 87 nations establishes unprecedented global data privacy protections.',
        3, 7, 'published', null, 0, 8932, '2025-01-14 14:30:00'
    ],
    // Article 3
    [
        'Quantum Computing Milestone: 1000-Qubit Processor Achieved',
        'quantum-computing-milestone-1000-qubit-processor',
        '<p>IBM has announced the successful development and testing of its Condor processor, a 1,121-qubit quantum computing chip that represents a significant leap forward in the quest for practical quantum computing.</p>
<p>The processor, developed at IBM\'s Thomas J. Watson Research Center, utilizes a new hexagonal qubit arrangement that dramatically reduces error rates compared to previous architectures. In benchmark tests, the Condor chip maintained coherence times of over 400 microseconds, nearly double the previous record.</p>
<p>"We\'re entering the era where quantum computers can begin tackling problems that are genuinely beyond the reach of classical supercomputers," said Dr. Jay Gambetta, VP of IBM Quantum. "The Condor processor brings us closer to quantum advantage in real-world applications."</p>
<p>The implications for fields such as drug discovery, materials science, and cryptography are profound. Early simulations on the Condor chip have successfully modeled molecular interactions for potential pharmaceutical compounds in minutes, tasks that would take classical supercomputers months to complete.</p>
<p>The cryptographic implications have also drawn attention from national security agencies worldwide. Post-quantum cryptography standards, published by NIST in 2024, are now being adopted with increased urgency as the timeline to cryptographically relevant quantum computing appears to be shortening.</p>
<p>IBM plans to make the Condor processor available through its cloud quantum computing platform by mid-2025, allowing researchers worldwide to run experiments on the new hardware.</p>',
        'IBM\'s 1,121-qubit Condor processor achieves record coherence times, pushing quantum computing closer to practical applications.',
        1, 3, 'published', null, 0, 12105, '2025-01-13 11:00:00'
    ],
    // Article 4
    [
        'Ancient Mediterranean Shipwreck Reveals Lost Trade Routes',
        'ancient-mediterranean-shipwreck-reveals-lost-trade-routes',
        '<p>Marine archaeologists from the University of Athens have uncovered a remarkably preserved 2,300-year-old shipwreck off the coast of Crete that is rewriting our understanding of ancient Mediterranean trade networks.</p>
<p>The vessel, dating to approximately 280 BCE during the early Ptolemaic period, was discovered at a depth of 65 meters using advanced sonar mapping technology. Initial excavation has revealed a cargo hold containing over 400 amphoras, bronze implements, and unusual ceramic items that suggest previously unknown trade connections between Egypt and the western Mediterranean.</p>
<p>"What makes this find exceptional is the presence of Iberian pottery alongside Egyptian grain seals," explained Dr. Konstantinos Papadopoulos, lead archaeologist. "This suggests a direct trade route between Ptolemaic Egypt and the Iberian Peninsula that we had no archaeological evidence for until now."</p>
<p>Among the most significant finds are a set of bronze navigation instruments and a sealed container holding papyrus scrolls that are currently undergoing careful conservation treatment. Preliminary infrared imaging suggests the scrolls contain merchant records and possibly navigational charts.</p>
<p>The ship appears to have sunk during a sudden storm, as evidenced by the scattered but well-preserved nature of the cargo. The cold, low-oxygen conditions at the site have preserved organic materials that would typically decompose within decades.</p>
<p>A full excavation is planned for the summer of 2025, with funding from the European Research Council and the Onassis Foundation.</p>',
        'A 2,300-year-old shipwreck near Crete reveals previously unknown Ptolemaic-era trade routes between Egypt and Iberia.',
        5, 7, 'published', null, 1, 9876, '2025-01-12 08:45:00'
    ],
    // Article 5
    [
        'New Study Links Urban Green Spaces to Improved Mental Health',
        'new-study-urban-green-spaces-mental-health',
        '<p>A comprehensive 10-year longitudinal study conducted across 15 major European cities has found compelling evidence that access to urban green spaces significantly reduces the incidence of anxiety and depression among city dwellers.</p>
<p>The research, published in The Lancet Planetary Health, followed 2.4 million participants and found that individuals living within 300 meters of a park or green area showed a 23% lower rate of clinical anxiety and a 17% lower rate of depression compared to those without nearby green spaces.</p>
<p>"The effect sizes we observed are remarkable and comparable to the benefits of regular exercise or social engagement," said Professor Ingrid Magnusson of the Karolinska Institute, the study\'s principal investigator. "Urban planning decisions are health decisions."</p>
<p>The study controlled for socioeconomic factors, pre-existing health conditions, and seasonal variations, finding that the benefits of green space access were consistent across income levels and age groups. However, the effects were most pronounced among elderly residents and those in lower-income neighborhoods.</p>
<p>Brain imaging data from a subset of 5,000 participants revealed measurable differences in cortisol levels and amygdala activity, suggesting that regular exposure to natural environments has concrete neurological effects on stress regulation.</p>
<p>The findings have prompted several European municipalities to accelerate urban greening programs. Barcelona, Copenhagen, and Vienna have already announced expanded park development initiatives citing the study\'s results.</p>',
        'A 10-year study of 2.4 million Europeans reveals that proximity to green spaces reduces anxiety by 23% and depression by 17%.',
        2, 4, 'published', null, 0, 7654, '2025-01-11 10:15:00'
    ],
    // Article 6
    [
        'Tech Giants Face Unprecedented Antitrust Challenges in Asia',
        'tech-giants-antitrust-challenges-asia',
        '<p>Major technology companies are facing a coordinated wave of antitrust enforcement across Asian markets, with regulators in Japan, South Korea, and India simultaneously launching investigations into alleged anti-competitive practices by Google, Apple, and Meta.</p>
<p>Japan\'s Fair Trade Commission announced a formal investigation into Google\'s search advertising practices, alleging that the company has leveraged its dominant market position to disadvantage local competitors. South Korea\'s Korea Fair Trade Commission has expanded its ongoing investigation of Apple\'s App Store policies, while India\'s Competition Commission has opened new proceedings against Meta regarding WhatsApp\'s data-sharing practices.</p>
<p>"We are seeing a new paradigm in global tech regulation," said Professor Akiko Tanaka of Tokyo University\'s Digital Economy Institute. "Asian regulators are no longer following the EU\'s lead—they are developing their own enforcement approaches tailored to their markets."</p>
<p>The combined potential fines across all three jurisdictions could exceed $8 billion, according to estimates from investment bank Goldman Sachs. More significantly, the investigations could force structural changes to how these companies operate in the fast-growing Asian market.</p>
<p>Industry analysts note that this coordinated enforcement effort reflects growing concerns about data sovereignty and digital market dominance among Asian nations. The timing coincides with the rise of competitive local alternatives in each market.</p>',
        'Japan, South Korea, and India launch coordinated antitrust investigations into Google, Apple, and Meta, signaling a new era of Asian tech regulation.',
        4, 6, 'published', null, 0, 6543, '2025-01-10 16:00:00'
    ],
    // Article 7
    [
        'CRISPR Gene Therapy Shows Promise in Rare Disease Treatment',
        'crispr-gene-therapy-rare-disease-treatment',
        '<p>A groundbreaking clinical trial at Massachusetts General Hospital has demonstrated the potential of CRISPR-Cas9 gene editing to treat Duchenne muscular dystrophy (DMD), a devastating genetic condition affecting approximately 1 in 3,500 male births worldwide.</p>
<p>The Phase II trial, involving 24 patients aged 6-14, showed that a single infusion of the CRISPR-based therapy restored dystrophin production to 38-52% of normal levels in skeletal muscle tissue. This level of restoration is considered clinically significant, as patients with even 10% of normal dystrophin levels typically show markedly milder symptoms.</p>
<p>"For the first time, we\'re seeing functional improvement in boys who were on a trajectory of progressive muscle deterioration," said Dr. Robert Finkel, the trial\'s principal investigator. "Several participants have shown improved mobility and cardiac function at the 12-month follow-up."</p>
<p>Unlike previous gene therapy approaches that relied on delivering a functional copy of the massive dystrophin gene, the CRISPR approach precisely corrects the genetic mutation responsible for the disease. This targeted correction results in the production of full-length, fully functional dystrophin protein.</p>
<p>Safety monitoring has revealed no serious adverse events related to the therapy, though mild inflammatory responses were observed in 30% of participants in the first week following infusion. All inflammatory events resolved with standard anti-inflammatory treatment.</p>
<p>The FDA has granted the therapy Breakthrough Therapy designation, and a pivotal Phase III trial is expected to begin enrollment in September 2025.</p>',
        'Phase II CRISPR trial restores dystrophin to 38-52% of normal levels in Duchenne muscular dystrophy patients, showing functional improvement.',
        7, 4, 'published', null, 1, 11234, '2025-01-09 12:30:00'
    ],
    // Article 8
    [
        'The Future of Remote Work: What Five Years of Data Tell Us',
        'future-remote-work-five-years-data',
        '<p>Five years after the COVID-19 pandemic fundamentally altered work patterns worldwide, a comprehensive analysis of remote work data reveals surprising trends that challenge both advocates and critics of distributed work arrangements.</p>
<p>The analysis, conducted by Stanford University\'s Institute for Economic Policy Research, synthesized data from 4,200 companies across 35 countries, spanning the period from 2020 to 2025. The findings paint a nuanced picture that defies simple narratives.</p>
<p>Productivity metrics show that fully remote workers are, on average, 7% more productive than their in-office counterparts for individual tasks, but 12% less productive for collaborative projects requiring real-time creative input. Hybrid arrangements—specifically those with 2-3 days per week in office—showed the optimal balance, with no productivity decline in either individual or collaborative work.</p>
<p>"The remote work debate has been far too binary," argues Professor Nicholas Bloom, the study\'s lead author. "The data overwhelmingly supports hybrid models, but the specifics matter enormously. A poorly designed hybrid policy can be worse than either extreme."</p>
<p>Employee retention data was perhaps the most striking finding: companies offering genuine flexibility saw 31% lower turnover rates and reported 28% higher employee satisfaction scores. The economic value of reduced turnover alone was estimated to save the average mid-size company $4.2 million annually.</p>
<p>However, the study also identified concerning trends in career advancement. Remote workers received 15% fewer promotions than hybrid or in-office peers, a gap that persisted even after controlling for performance ratings. This "proximity bias" was most pronounced in traditional industries and diminished in tech-forward companies with established remote work cultures.</p>',
        'Stanford\'s five-year analysis of 4,200 companies reveals hybrid work as the optimal model, while highlighting persistent promotion gaps for remote workers.',
        6, 8, 'published', null, 0, 8765, '2025-01-08 09:30:00'
    ],
    // Article 9
    [
        'Mars Sample Return Mission Enters Critical Phase',
        'mars-sample-return-mission-critical-phase',
        '<p>NASA and the European Space Agency (ESA) have announced that the Mars Sample Return (MSR) mission has entered its most critical phase, with the Sample Retrieval Lander scheduled for launch in the July 2026 window.</p>
<p>The mission, which aims to bring back 30 carefully sealed tubes of Martian rock and soil collected by the Perseverance rover, represents the most complex robotic space mission ever attempted. The samples, collected from the ancient Jezero Crater lake bed, are considered the most scientifically valuable material in planetary science.</p>
<p>"These samples could answer the most profound question in science: has life ever existed beyond Earth?" said Dr. Meenakshi Wadhwa, MSR principal scientist. "The analytical tools we have on Earth are orders of magnitude more sensitive than anything we can send to Mars."</p>
<p>The redesigned mission architecture, announced after the 2024 cost review, utilizes a novel approach with two smaller landers instead of the original single large lander. The first will deploy a small rocket (the Mars Ascent Vehicle) to launch the samples into orbit, where an ESA-built Earth Return Orbiter will capture them and begin the two-year journey home.</p>
<p>Total mission cost is now estimated at $7.8 billion, down from the initial $11 billion estimate following the architectural redesign. Samples are expected to arrive at Earth in 2033, where they will be transported to a specially built Biosafety Level 5 facility in Houston.</p>',
        'NASA-ESA Mars Sample Return mission advances, aiming to bring back Perseverance\'s Jezero Crater samples by 2033.',
        2, 3, 'published', null, 0, 10432, '2025-01-07 15:00:00'
    ],
    // Article 10
    [
        'Cryptocurrency Regulation Framework Adopted by 40 Nations',
        'cryptocurrency-regulation-framework-40-nations',
        '<p>The Financial Action Task Force (FATF) has announced that 40 nations have formally adopted the International Cryptocurrency Regulation Framework (ICRF), establishing standardized rules for digital asset exchanges, stablecoin issuers, and decentralized finance (DeFi) protocols.</p>
<p>The framework, which has been in development since 2022, requires cryptocurrency exchanges to implement Know Your Customer (KYC) procedures equivalent to those required of traditional banks, and mandates that stablecoin issuers maintain fully auditable reserves. DeFi protocols that exceed $100 million in total value locked must register with national financial regulators.</p>
<p>"This framework strikes a balance between fostering innovation and protecting consumers," said FATF President Dr. Elisa de Anda Madrazo. "Unregulated cryptocurrency markets have been exploited for money laundering, tax evasion, and fraud. These rules bring necessary oversight while preserving the benefits of blockchain technology."</p>
<p>Market reaction has been cautiously positive, with Bitcoin rising 3.2% in the 24 hours following the announcement. Industry groups noted that regulatory clarity removes a significant barrier to institutional adoption.</p>
<p>The framework includes provisions for cross-border enforcement cooperation and establishes a dispute resolution mechanism for cases involving multiple jurisdictions. Signatory nations have 24 months to implement the required legislative changes.</p>',
        'FATF announces 40-nation adoption of cryptocurrency regulation framework covering exchanges, stablecoins, and DeFi protocols.',
        4, 6, 'published', null, 0, 5678, '2025-01-06 11:45:00'
    ],
    // Article 11
    [
        'Deep Ocean Exploration Discovers New Species Near Hydrothermal Vents',
        'deep-ocean-exploration-new-species-hydrothermal-vents',
        '<p>A joint expedition by the Schmidt Ocean Institute and NOAA has discovered an extraordinary ecosystem of previously unknown species thriving near hydrothermal vents in the Mariana Trough, at depths exceeding 4,000 meters.</p>
<p>The expedition, conducted aboard the research vessel Falkor (too), deployed remotely operated vehicles (ROVs) that documented at least 14 species new to science, including a translucent octopus with bioluminescent tentacle tips, three species of extremophile tube worms, and a colonial organism that appears to blur the boundary between individual and colony.</p>
<p>"We are discovering ecosystems that operate on entirely different energy sources than sunlight-based ecosystems on the surface," said Dr. Wendy Schmidt, co-founder of the Schmidt Ocean Institute. "These organisms are powered by chemical energy from the Earth\'s interior, and they have evolved remarkable adaptations."</p>
<p>Of particular scientific interest is a species of shrimp-like crustacean that appears to cultivate chemosynthetic bacteria in specialized structures on its carapace, a form of farming behavior previously unknown in deep-sea organisms.</p>
<p>DNA analysis of collected specimens is underway at NOAA\'s Northwest Fisheries Science Center. Preliminary results suggest that several of the new species represent not just new species but potentially new genera, indicating long evolutionary isolation.</p>',
        'Scientists discover 14 new species near deep-sea hydrothermal vents in the Mariana Trough.',
        2, 4, 'published', null, 0, 7890, '2025-01-05 13:20:00'
    ],
    // Article 12
    [
        'Electric Aviation: First Commercial eVTOL Routes Announced',
        'electric-aviation-first-commercial-evtol-routes',
        '<p>Joby Aviation has received final FAA certification to begin commercial air taxi operations in the Los Angeles metropolitan area, marking the beginning of the electric vertical takeoff and landing (eVTOL) era in commercial aviation.</p>
<p>The company will launch three initial routes connecting LAX Airport to downtown Los Angeles, Santa Monica, and Hollywood, with flights beginning in April 2025. The five-seat aircraft can travel at speeds of up to 200 mph with a range of 150 miles on a single charge, completing the LAX-to-downtown trip in approximately 8 minutes compared to the typical 45-90 minute ground journey.</p>
<p>"This isn\'t science fiction anymore—it\'s a commercial reality," said Joby CEO JoeBen Bevirt. "We have the aircraft, the certification, and the infrastructure. Now we\'re ready to transform urban transportation."</p>
<p>Ticket prices for the initial routes will start at $99 per seat, a premium over ground transportation but competitive with helicopter services. The company plans to reduce prices as volume increases and battery technology improves.</p>
<p>Six vertiports—dedicated takeoff and landing facilities—have been constructed across the LA area, each equipped with rapid charging infrastructure capable of returning an aircraft to full charge in 10 minutes. The vertiports feature automated flight management systems developed in partnership with NASA\'s Advanced Air Mobility program.</p>
<p>Uber, Archer Aviation, and Lilium are expected to launch competing services in other major US cities within 12-18 months.</p>',
        'Joby Aviation receives FAA certification for the first commercial eVTOL air taxi routes in Los Angeles.',
        1, 3, 'published', null, 0, 9234, '2025-01-04 10:00:00'
    ],
    // Article 13 - DRAFT by sarah_editor (Flag 4)
    [
        'The Hidden Archive of Digital Artifacts',
        'the-hidden-archive-of-digital-artifacts',
        '<p>In an investigation spanning three continents and eight months, Nac News has uncovered a vast digital preservation operation being conducted in secret by a consortium of technology companies and academic institutions.</p>
<p>The operation, internally referred to as "Project Mosaic," involves the systematic archiving of at-risk digital content—websites, forums, social media posts, and governmental records—that are being removed or altered at an alarming rate across the internet.</p>
<p>Sources within the consortium, who spoke on condition of anonymity, revealed that the archive now contains over 800 petabytes of preserved digital content, stored in hardened data centers in three undisclosed locations. The project was initiated in 2021 after researchers documented a 340% increase in digital content removal by governments worldwide.</p>
<p>"History is being erased in real time," said one source familiar with the project. "Governments and corporations are scrubbing inconvenient truths from the digital record. We believe future generations have a right to the unaltered historical record."</p>
<p>The consortium includes representatives from major technology companies, several Ivy League universities, and at least two European national libraries. Funding comes from a combination of corporate contributions and anonymous philanthropic donations totaling over $2 billion since the project\'s inception.</p>
<p>[EDITOR NOTE: This story requires additional source verification before publication. Legal review pending for potential national security implications. Hold for Sarah\'s approval. - Mark T.]</p>',
        'Investigation reveals a secret multi-billion dollar operation to preserve at-risk digital content being removed by governments worldwide.',
        1, 5, 'draft', null, 0, 0, NULL
    ],
    // Article 14
    [
        'Advances in Solid-State Battery Technology Promise Faster EV Charging',
        'advances-solid-state-battery-technology-ev-charging',
        '<p>Toyota has announced a breakthrough in solid-state battery technology that could enable electric vehicles to charge from 10% to 80% in under 10 minutes, potentially eliminating one of the biggest barriers to widespread EV adoption.</p>
<p>The new battery cells, developed at Toyota\'s research facility in Susono, Japan, utilize a sulfide-based solid electrolyte that allows lithium ions to move at speeds comparable to liquid electrolytes while maintaining the safety and energy density advantages of solid-state designs.</p>
<p>"We have achieved the charging speed of liquid electrolyte batteries with the safety profile of solid-state technology," said Keiji Kaita, president of Toyota\'s research division. "This combination has been the holy grail of battery research for over a decade."</p>
<p>The batteries also demonstrate significantly improved longevity, retaining 90% of their capacity after 2,000 charge cycles—roughly equivalent to 600,000 miles of driving. Current lithium-ion batteries typically degrade to 80% capacity after 1,000-1,500 cycles.</p>
<p>Toyota plans to introduce the technology in production vehicles by 2027, starting with its luxury Lexus brand before expanding to mainstream models. The company estimates that mass production will be feasible by 2028, with costs competitive with current lithium-ion pack pricing.</p>',
        'Toyota announces solid-state battery breakthrough enabling sub-10-minute EV charging while maintaining 90% capacity after 2,000 cycles.',
        1, 3, 'published', null, 0, 6789, '2025-01-03 14:15:00'
    ],
];

$stmt = $conn->prepare("INSERT INTO articles (title, slug, content, excerpt, category_id, author_id, status, featured_image, is_featured, views, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($articles as $idx => $a) {
    $stmt->bind_param('ssssiisssis', $a[0], $a[1], $a[2], $a[3], $a[4], $a[5], $a[6], $a[7], $a[8], $a[9], $a[10]);
    $stmt->execute();
}
$stmt->close();

echo "[*] Seeding tags...\n";

// === TAGS ===
$tags = [
    [1, 'AI', 'ai'],
    [2, 'Climate', 'climate'],
    [3, 'Privacy', 'privacy'],
    [4, 'Quantum', 'quantum'],
    [5, 'Archaeology', 'archaeology'],
    [6, 'Mental Health', 'mental-health'],
    [7, 'Antitrust', 'antitrust'],
    [8, 'Gene Therapy', 'gene-therapy'],
    [9, 'Remote Work', 'remote-work'],
    [10, 'Mars', 'mars'],
    [11, 'Cryptocurrency', 'cryptocurrency'],
    [12, 'Ocean', 'ocean'],
    [13, 'Aviation', 'aviation'],
    [14, 'Battery', 'battery'],
    [15, 'Electric Vehicle', 'electric-vehicle'],
];

$stmt = $conn->prepare("INSERT INTO tags (id, name, slug) VALUES (?, ?, ?)");
foreach ($tags as $t) {
    $stmt->bind_param('iss', $t[0], $t[1], $t[2]);
    $stmt->execute();
}
$stmt->close();

// Article-Tag mappings
$articleTags = [
    [1, 1], [1, 2],    // Article 1: AI, Climate
    [2, 3],             // Article 2: Privacy
    [3, 4],             // Article 3: Quantum
    [4, 5],             // Article 4: Archaeology
    [5, 6],             // Article 5: Mental Health
    [6, 7],             // Article 6: Antitrust
    [7, 8],             // Article 7: Gene Therapy
    [8, 9],             // Article 8: Remote Work
    [9, 10],            // Article 9: Mars
    [10, 11],           // Article 10: Cryptocurrency
    [11, 12],           // Article 11: Ocean
    [12, 13],           // Article 12: Aviation
    [13, 1],            // Article 13: AI (draft)
    [14, 14], [14, 15], // Article 14: Battery, EV
];

$stmt = $conn->prepare("INSERT INTO article_tags (article_id, tag_id) VALUES (?, ?)");
foreach ($articleTags as $at) {
    $stmt->bind_param('ii', $at[0], $at[1]);
    $stmt->execute();
}
$stmt->close();

echo "[*] Seeding comments...\n";

// === COMMENTS ===
$comments = [
    [1, 9, null, null, 'This is incredible! The accuracy improvement over previous models is staggering. I wonder how this will affect insurance models for climate-related risks.', 'approved'],
    [1, 10, null, null, 'As someone who works in agriculture, this could be a game-changer for crop planning. We need more tools like this.', 'approved'],
    [1, null, 'Sarah K.', 'sarah.k@example.com', 'I\'m curious about the computational resources needed to run this model. Is it accessible to smaller research institutions?', 'approved'],
    [3, 10, null, null, 'The cryptography implications are what worry me most. Are we really prepared for quantum-capable attacks on current encryption?', 'approved'],
    [3, 12, null, null, 'Post-quantum cryptography can\'t come fast enough. Every organization should be planning their migration now.', 'approved'],
    [4, 9, null, null, 'The Ptolemaic era is so fascinating. I hope the papyrus scrolls reveal more about ancient navigation techniques!', 'approved'],
    [5, 14, null, null, 'As a city planner, this study confirms what we\'ve been advocating for years. Green infrastructure is health infrastructure.', 'approved'],
    [7, 11, null, null, 'This gives so much hope to families dealing with DMD. Medical science is truly amazing.', 'approved'],
    [7, 9, null, null, 'The fact that a single infusion can produce these results is remarkable. Looking forward to the Phase III results.', 'approved'],
    [8, 12, null, null, 'The promotion gap for remote workers is concerning but not surprising. Visibility still matters in most organizations.', 'approved'],
    [9, 10, null, null, 'The cost reduction is welcome news. $7.8B is still massive but the scientific value is incalculable.', 'approved'],
    [12, 14, null, null, '$99 for an 8-minute trip? Sign me up! This could genuinely transform LA commuting.', 'approved'],
    [12, null, 'AviationFan', 'avfan@example.com', 'I\'m skeptical about the 10-minute charging claim. Battery degradation at those charging rates must be significant.', 'approved'],
    [14, 11, null, null, 'Toyota has been quietly working on solid-state for years. Glad to see it finally coming to production.', 'approved'],
];

$stmt = $conn->prepare("INSERT INTO comments (article_id, user_id, author_name, author_email, content, status) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($comments as $c) {
    $stmt->bind_param('iissss', $c[0], $c[1], $c[2], $c[3], $c[4], $c[5]);
    $stmt->execute();
}
$stmt->close();

echo "[*] Seeding internal memos...\n";

// === INTERNAL MEMOS (Flag 2 - found via SQL injection) ===
$memos = [
    [1, 'Q1 2025 Editorial Calendar', 'Editorial priorities for Q1: Focus on AI developments, climate technology, and space exploration. Assign at least 3 reporters to CES coverage. Budget approved for Mars sample return series.', 'medium', 'editorial', 1],
    [2, 'Infrastructure Migration Update', 'Migration to new cloud infrastructure is 60% complete. Database optimization reduced query times by 40%. CDN integration pending. All staff should update their SSH keys by end of month.', 'high', 'technology', 1],
    [3, 'Project Lighthouse - Confidential', 'Project Lighthouse status update: The new investigative journalism platform is in beta testing. Codename must remain confidential. Access restricted to senior editorial staff only. Expected launch date: Q3 2025. Contact Mark Thompson for access credentials.', 'critical', 'management', 1],
    [4, 'Office Security Procedures Update', 'Reminder: All staff must use badge access for server room entry. Visitor logs should be maintained at reception. IT team to conduct quarterly security audit next week.', 'low', 'security', 1],
    [5, 'Freelancer Payment Schedule', 'Freelancer payments for December have been processed. New freelancer rate card effective January 1st. All department heads should submit updated freelancer rosters by January 15th.', 'medium', 'finance', 1],
    [6, 'Internal API Migration Notice', 'Reminder: The internal API service has been migrated to Docker. New base URL is http://internal-api:5000 (accessible only within nac-net). Endpoints: /status, /health, /debug/info. All teams must update their integration configs before January 30th. Do NOT expose this service to public network.', 'high', 'engineering', 1],
];

$stmt = $conn->prepare("INSERT INTO internal_memos (id, subject, content, priority, department, created_by) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($memos as $m) {
    $stmt->bind_param('issssi', $m[0], $m[1], $m[2], $m[3], $m[4], $m[5]);
    $stmt->execute();
}
$stmt->close();

echo "[*] Seeding admin secrets...\n";

// === ADMIN SECRETS (Flag 8 - XOR encrypted with key "PTOLEMY") ===
// Encryption: base64(XOR(plaintext, repeating key "PTOLEMY"))
// Plaintext: "the_great_library_never_burned"
function xor_encrypt($plaintext, $key) {
    $result = '';
    $keyLen = strlen($key);
    for ($i = 0; $i < strlen($plaintext); $i++) {
        $result .= chr(ord($plaintext[$i]) ^ ord($key[$i % $keyLen]));
    }
    return base64_encode($result);
}

$encrypted_vault = xor_encrypt('the_great_library_never_burned', 'PTOLEMY');
$encrypted_backup = xor_encrypt('backup_verified_2025_01_15', 'PTOLEMY');
$encrypted_api = xor_encrypt('master_key_nac_prime', 'PTOLEMY');

$secrets = [
    [1, 'vault_access_code', $encrypted_vault, 'internal'],
    [2, 'backup_verification', $encrypted_backup, 'internal'],
    [3, 'master_api_key', $encrypted_api, 'internal'],
];

$stmt = $conn->prepare("INSERT INTO admin_secrets (id, key_name, encrypted_value, encryption_method) VALUES (?, ?, ?, ?)");
foreach ($secrets as $s) {
    $stmt->bind_param('isss', $s[0], $s[1], $s[2], $s[3]);
    $stmt->execute();
}
$stmt->close();

echo "[*] Seeding site settings...\n";

// === SITE SETTINGS ===
$settings = [
    ['site_name', 'Nac News'],
    ['site_description', 'Your trusted source for technology, science, and world news'],
    ['site_url', 'https://nac-news.com'],
    ['admin_email', 'admin@nac-news.com'],
    ['posts_per_page', '10'],
    ['allow_comments', '1'],
    ['allow_registration', '1'],
    ['maintenance_mode', '0'],
    ['analytics_id', 'UA-XXXXX-1'],
    ['social_twitter', 'https://twitter.com/nacnews'],
    ['social_facebook', 'https://facebook.com/nacnews'],
    ['copyright_text', '© 2025 Nac News. All rights reserved.'],
    ['encryption_key', 'PTOLEMY'],
];

$stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
foreach ($settings as $s) {
    $stmt->bind_param('ss', $s[0], $s[1]);
    $stmt->execute();
}
$stmt->close();

echo "[*] Seeding pages...\n";

// === PAGES ===
$pages = [
    [1, 'About Us', 'about', '<h2>About Nac News</h2><p>Nac News is a leading digital news platform delivering comprehensive coverage of technology, science, world affairs, business, culture, and more.</p><p>Founded in 2019, we are committed to providing accurate, in-depth reporting that helps our readers understand the complex world around them. Our team of experienced journalists and analysts works tirelessly to bring you the stories that matter.</p><h3>Our Mission</h3><p>To illuminate truth through rigorous journalism, making quality news accessible to everyone in the digital age.</p><h3>Contact</h3><p>Email: contact@nac-news.com<br>Phone: +1-555-0100<br>Address: 1247 Library Avenue, Suite 400, Boston, MA 02101</p>', 'default', 1],
    [2, 'Privacy Policy', 'privacy', '<h2>Privacy Policy</h2><p>Last updated: January 1, 2025</p><p>Nac News ("we," "our," or "us") respects your privacy and is committed to protecting your personal data. This privacy policy explains how we collect, use, and safeguard your information when you visit our website.</p><h3>Information We Collect</h3><p>We collect information you provide directly, such as when you create an account, comment on articles, or contact us. This may include your name, email address, and any other information you choose to provide.</p><h3>How We Use Your Information</h3><p>We use your information to provide and improve our services, personalize your experience, send newsletters (with your consent), and respond to your inquiries.</p><h3>Data Security</h3><p>We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction.</p>', 'default', 1],
    [3, 'Terms of Service', 'terms', '<h2>Terms of Service</h2><p>Last updated: January 1, 2025</p><p>By accessing and using Nac News, you agree to be bound by these Terms of Service.</p><h3>User Accounts</h3><p>You are responsible for maintaining the confidentiality of your account credentials. You agree to accept responsibility for all activities that occur under your account.</p><h3>Content</h3><p>All content published on Nac News is protected by copyright. You may not reproduce, distribute, or create derivative works without explicit permission.</p><h3>User Conduct</h3><p>Users agree not to post content that is defamatory, obscene, threatening, or otherwise objectionable. We reserve the right to remove any content that violates these terms.</p>', 'default', 1],
];

$stmt = $conn->prepare("INSERT INTO pages (id, title, slug, content, template, is_published) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($pages as $p) {
    $stmt->bind_param('issssi', $p[0], $p[1], $p[2], $p[3], $p[4], $p[5]);
    $stmt->execute();
}
$stmt->close();

echo "[+] Seeding complete! All data inserted successfully.\n";
$conn->close();
