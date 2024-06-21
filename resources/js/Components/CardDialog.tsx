import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  TextField,
} from "@mui/material";
import { FC, useEffect } from "react";
import { SubmitHandler, useForm } from "react-hook-form";

type CardDialogProps = {
  open: boolean;
  onClose: () => void;
  onSave: (title: string) => void;
  initialTitle: string;
};

type FormValues = {
  title: string;
};

const CardDialog: FC<CardDialogProps> = ({ open, onClose, onSave, initialTitle }) => {
  const {
    register,
    handleSubmit,
    reset,
    formState: { isValid, errors },
  } = useForm<FormValues>();

  useEffect(() => {
    reset({ title: initialTitle });
  }, [initialTitle, reset]);

  const onSubmit: SubmitHandler<FormValues> = (data) => {
    onSave(data.title);
    reset({ title: "" });
  };

  const handleClose = () => {
    onClose();
    reset({ title: "" });
  };

  return (
    <Dialog open={open} onClose={handleClose} fullWidth>
      <DialogTitle>{initialTitle ? "編集" : "追加"}</DialogTitle>
      <DialogContent>
        <form onSubmit={handleSubmit(onSubmit)}>
          <TextField
            multiline
            autoFocus
            margin="dense"
            label="やること"
            type="text"
            fullWidth
            {...register("title", { required: "Title is required" })}
            error={!!errors.title}
            helperText={errors.title?.message}
          />
          <DialogActions>
            <Button onClick={handleClose} color="primary">
              キャンセル
            </Button>
            <Button type="submit" color="primary" disabled={!isValid}>
              保存
            </Button>
          </DialogActions>
        </form>
      </DialogContent>
    </Dialog>
  );
};

export default CardDialog;
