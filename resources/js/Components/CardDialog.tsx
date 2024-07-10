import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  TextField,
} from "@mui/material";
import { SubmitHandler, useForm } from "react-hook-form";
import { useTodoBoard } from "./useTodoBoard";
import { useMemo } from "react";

export type DialogMode = "add" | "edit";

type CardDialogProps = {
  open: boolean;
  onClose: () => void;
  onSave: (title: string) => void;
};

export type FormValues = {
  title: string;
};

const CardDialog: React.FC<CardDialogProps> = ({ open, onClose, onSave }) => {
  const dialogMode = useTodoBoard((state) => state.dialogMode);
  const setResetCardDialogForm = useTodoBoard((state) => state.setResetCardDialogForm);

  const {
    register,
    handleSubmit,
    reset,
    formState: { isValid, errors },
  } = useForm<FormValues>();

  useMemo(() => setResetCardDialogForm(reset), [setResetCardDialogForm, reset]);

  const onSubmit: SubmitHandler<FormValues> = (data) => {
    onSave(data.title);
  };

  const handleClose = () => {
    onClose();
  };

  return (
    <Dialog open={open} onClose={handleClose} fullWidth>
      <DialogTitle>{dialogMode === "add" ? "追加" : "編集"}</DialogTitle>
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
