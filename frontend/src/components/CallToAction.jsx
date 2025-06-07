import { HeroButton } from "./HeroButton";

export function CallToAction() {
  return (
    <div className="flex justify-center items-center mt-16 mb-20">
      <div
        className="
          w-[90%] 
          bg-[#0062FF] 
          text-center 
          px-8 
          py-12 sm:py-10  /* padding vertical plus flexible */
          flex flex-col 
          justify-center 
          items-center 
          rounded-xl 
          gap-5
        "
      >
        {/* Texte d'accroche */}
        <p className="font-Poppins text-lg font-medium text-white">
          Envie d’en voir plus ? Nos prochains événements n’attendent que vous.
        </p>

        {/* CTA */}
        <HeroButton
          to="/events"
          className="bg-white !text-[#0062FD] hover:bg-gray-300 md:!w-72"
          aria-label="Voir la liste des prochains événements"
        />
      </div>
    </div>
  );
}
