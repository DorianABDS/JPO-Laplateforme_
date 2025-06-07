import { Button } from "./Button";

export function CallToAction() {
  return (
    <div className="flex justify-center items-center mt-16 mb-20 ">
      <div className="h-48 sm:h-44 w-[90%] bg-gradient-to-br 
                    from-[#0062FF] via-[#0052CC] to-[#0041AA] text-center px-8 flex flex-col justify-center items-center rounded-xl gap-5">
        <p className="font-Poppins text-lg font-medium">
          Envie d’en voir plus ? Nos prochains événements n’attendent que vous.</p>
        <Button
          to="/events"
          label = "Explorer nos JPO"
          className="text-lg sm:text-2xl font-semibold h-14 md:!w-72" 
        />
      </div>
    </div>
  )
}
