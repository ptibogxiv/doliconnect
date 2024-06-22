import {
  JpegError,
  JpegImage,
  setVerbosityLevel,
  VerbosityLevel,
} from "../../build/image_decoders/pdf.image_decoders.mjs";

// Avoid unnecessary console "spam", by ignoring `info`/`warn` calls.
setVerbosityLevel(VerbosityLevel.ERRORS);

const ignored = ["Cannot read properties"];

function ignoredError(error) {
  if (error instanceof JpegError) {
    return true;
  }
  return ignored.some(message => error.message.includes(message));
}

/**
 * @param {Buffer} data
 */
function fuzz(data) {
  try {
    new JpegImage().parse(new Uint8Array(data));
  } catch (error) {
    if (error.message && !ignoredError(error)) {
      throw error;
    }
  }
}

export { fuzz };
