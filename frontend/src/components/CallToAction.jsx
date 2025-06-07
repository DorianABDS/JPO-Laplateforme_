import { HeroButton } from "./HeroButton";

export function CallToAction() {
  return (
    <div className="flex justify-center items-center mt-16 mb-20">
      <div className="h-48 sm:h-44 w-[90%] bg-[#0062FF] text-center px-8 flex flex-col justify-center items-center rounded-xl gap-5">
        <p className="font-Poppins text-lg font-medium">
          Envie d’en voir plus ? Nos prochains événements n’attendent que vous.</p>
        <HeroButton
          to="/events"
          className=" bg-white !text-[#0062FD] hover:bg-gray-300 md:!w-72" 
        />
      </div>
    </div>
  )
}
