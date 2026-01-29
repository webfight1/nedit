<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<footer class="site-footer mt-[50px] bg-[#eef1f8] pt-10 pb-6 text-sm text-slate-800">
	<div class="max-w-[1200px] mx-auto px-4">
		<div class="flex flex-col md:flex-row md:items-start md:justify-between gap-8 border-b border-slate-300 pb-8">
			<!-- Logo + payments -->
			<div class="space-y-4 max-w-xs">
				<div>
					<div class="font-nailedit text-[24px] text-primary leading-none">NAILEDIT</div>
					<div class="text-[11px] uppercase tracking-[0.2em] text-slate-500 mt-1">Beauty Concept</div>
				</div>
				<div class="space-y-1">
					<div class="text-[11px] text-slate-500 uppercase">Makseviisid</div>
					<div class="flex items-center gap-3 text-[12px] font-semibold text-slate-700">
						<span class="inline-flex items-center justify-center rounded-[4px] bg-white px-2 py-1 border border-slate-200">VISA</span>
						<span class="inline-flex items-center justify-center rounded-[4px] bg-white px-2 py-1 border border-slate-200">Mastercard</span>
						<span class="inline-flex items-center justify-center rounded-[4px] bg-white px-2 py-1 border border-slate-200 text-[11px]">Maksekeskus</span>
					</div>
				</div>
			</div>

			<!-- Navigation -->
			<div class="grid grid-cols-2 gap-8 text-[13px] uppercase tracking-[0.08em]">
				<div class="space-y-2">
					<div class="font-semibold text-slate-700 mb-1">Menüü</div>
					<a href="#" class="block hover:text-primary">Avaleht</a>
					<a href="#" class="block hover:text-primary">E-pood</a>
					<a href="#" class="block hover:text-primary">Teenused</a>
					<a href="#" class="block hover:text-primary">Blogi</a>
				</div>
				<div class="space-y-2">
					<div class="font-semibold text-slate-700 mb-1">Info</div>
					<a href="#" class="block hover:text-primary">Tarne</a>
					<a href="#" class="block hover:text-primary">Tagastused</a>
					<a href="#" class="block hover:text-primary">Klienditugi</a>
				</div>
			</div>

			<!-- Contacts + social -->
			<div class="space-y-3 max-w-xs">
				<div class="font-semibold uppercase tracking-[0.08em] text-slate-700">Kontaktid</div>
				<div class="flex items-start gap-3">
					<div class="mt-[2px] text-primary">☎</div>
					<div class="space-y-1 text-[13px]">
						<div class="font-semibold text-slate-900">+372 5555 5555</div>
						<div class="text-slate-600">info@nailedit.ee</div>
						<div class="text-slate-500 text-[12px]">E–R 09:00 – 19:00</div>
					</div>
				</div>
				<div class="flex items-center gap-3 pt-1">
					<a href="#" class="w-8 h-8 rounded-full border border-slate-300 flex items-center justify-center text-slate-700 hover:border-primary hover:text-primary transition" aria-label="Instagram">
						<span class="text-[14px]">IG</span>
					</a>
					<a href="#" class="w-8 h-8 rounded-full border border-slate-300 flex items-center justify-center text-slate-700 hover:border-primary hover:text-primary transition" aria-label="TikTok">
						<span class="text-[14px]">TT</span>
					</a>
				</div>
			</div>
		</div>

		<!-- Bottom bar -->
		<div class="flex flex-col md:flex-row items-center justify-between gap-3 pt-4 text-[12px] text-slate-600">
			<a href="#" class="underline underline-offset-2 hover:text-primary">Privaatsuspoliitika</a>
			<div class="text-center">
				<?php echo esc_html( date_i18n( 'Y' ) ); ?> &copy; <?php bloginfo( 'name' ); ?>. Kõik õigused kaitstud.
			</div>
			<a href="#" class="underline underline-offset-2 hover:text-primary">Müügi tingimused</a>
		</div>
	</div>
</footer>

<button
	id="nailedit-scroll-top"
	type="button"
	class="fixed bottom-6 right-6 z-40 hidden md:flex items-center justify-center w-12 h-12 rounded-full border border-primary text-primary bg-white shadow-lg hover:bg-primary hover:text-white transition"
	aria-label="<?php echo esc_attr__( 'Tagasi üles', 'nailedit' ); ?>"
>
	<span class="text-xl leading-none">
		&uarr;
	</span>
</button>

<?php wp_footer(); ?>
</body>
</html>
